<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shipment;
use App\Models\WebSetting;
use App\Services\Courier\PathaoCourierService;
use App\Services\Courier\SteadfastCourierService;
use App\Services\InventoryService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use RuntimeException;

class SaleController extends Controller
{
    public function index()
    {
        // source_label is appended here so the list stays sortable on a plain
        // string rather than a nested relation.
        $sales = Order::with(['customer', 'user'])->latest()->get();
        $sales->each->append('source_label');

        return Inertia::render('Admin/Sales/Index', [
            'sales' => $sales,
        ]);
    }

    public function show($id)
    {
        $sale = Order::with(['customer', 'user', 'items.product', 'shipments'])->findOrFail($id);
        $sale->append('total_in_words');

        // A booking the courier refused carries its reason in the raw response;
        // spell it out so the screen can say what to fix.
        $sale->shipments->each(function ($shipment) {
            $shipment->failure_reason = blank($shipment->consignment_id) ? $shipment->failureReason() : null;
            $shipment->status_label = $shipment->statusLabel();
            $shipment->status_tone = $shipment->statusTone();
            $shipment->delivery_fee = $shipment->deliveryFee();
        });
        $settings = WebSetting::first();

        return Inertia::render('Admin/Sales/Show', [
            'sale' => $sale,
            'courierOptions' => [
                'pathao' => (new PathaoCourierService($settings ?? new WebSetting()))->enabled(),
                'steadfast' => (new SteadfastCourierService($settings ?? new WebSetting()))->enabled(),
            ],
            'liveShipment' => $sale->liveShipment(),
        ]);
    }

    /**
     * The same invoice the Show page prints, rendered straight to an A5 PDF.
     *
     * Served as a download rather than a print dialog, so the file that reaches
     * the customer is A5 whatever paper size the operator's browser defaults to.
     */
    public function invoicePdf($id)
    {
        $sale = Order::with(['customer', 'user', 'items.product'])->findOrFail($id);
        $sale->append('total_in_words');
        $settings = WebSetting::first();

        $pdf = Pdf::loadView('pdf.sale-invoice-a5', [
            'sale' => $sale,
            'shop' => $settings,
            'orderDate' => Carbon::parse($sale->created_at)->format('d M Y'),
            'logo' => $this->inlineImage($settings?->logo),
            'fonts' => [
                'regular' => $this->fontPath('NotoSansBengali-Regular.ttf'),
                'bold' => $this->fontPath('NotoSansBengali-Bold.ttf'),
            ],
        ])->setPaper('a5', 'portrait');

        return $pdf->download("Invoice-{$sale->order_number}.pdf");
    }

    /**
     * dompdf reads @font-face sources off disk; forward slashes so the same
     * declaration parses on Windows as on the Linux host.
     */
    private function fontPath(string $file): string
    {
        return str_replace('\\', '/', resource_path("fonts/{$file}"));
    }

    /**
     * Settings store the logo as a public path. Inlining it as a data URI keeps
     * the PDF renderer off the network, so the invoice looks the same whether or
     * not the host can reach its own site.
     */
    private function inlineImage(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $file = public_path(ltrim($path, '/'));

        if (! is_file($file)) {
            return null;
        }

        $mime = match (strtolower(pathinfo($file, PATHINFO_EXTENSION))) {
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            // SVG is not rasterised by dompdf without extra support; skip it
            // rather than print a broken image box.
            default => null,
        };

        return $mime ? 'data:'.$mime.';base64,'.base64_encode(file_get_contents($file)) : null;
    }

    public function destroy($id, InventoryService $inventory)
    {
        $sale = Order::with('items.product')->findOrFail($id);

        DB::transaction(function () use ($sale, $inventory): void {
            // A cancelled order has already handed its stock back. Any other
            // order still holds it, and once the record is gone there is
            // nothing left to reconcile against — so release it first.
            if ($sale->status !== 'cancelled') {
                $inventory->releaseOrderStock($sale, "Deleted order {$sale->order_number}");
            }

            $sale->delete();
        });

        return redirect()->route('admin.sales.index')->with('success', 'Sale record deleted and stock released.');
    }

    public function updateStatus(Request $request, $id, InventoryService $inventory)
    {
        $payload = $request->validate([
            'status' => ['required', 'in:pending,no_response,follow_up,on_hold,advance_payment,processing,shipped,delivered,cancelled,partially_returned,returned'],
            'payment_status' => ['required', 'in:unpaid,partial,paid'],
        ]);

        $sale = Order::with('items.product')->findOrFail($id);

        $wasCancelled = $sale->status === 'cancelled';
        $nowCancelled = $payload['status'] === 'cancelled';

        try {
            DB::transaction(function () use ($sale, $payload, $inventory, $wasCancelled, $nowCancelled): void {
                // Stock moves only when the order crosses into or out of
                // "cancelled", so re-saving the same status never double-counts.
                if (! $wasCancelled && $nowCancelled) {
                    $inventory->releaseOrderStock($sale, "Cancelled order {$sale->order_number}");
                } elseif ($wasCancelled && ! $nowCancelled) {
                    $inventory->reserveOrderStock($sale, "Reopened order {$sale->order_number}");
                }

                $sale->update([
                    'status' => $payload['status'],
                    'delivered_at' => $this->deliveredAtFor($sale, $payload['status']),
                    'payment_status' => $payload['payment_status'],
                    'paid_amount' => $payload['payment_status'] === 'paid' ? $sale->total : $sale->paid_amount,
                    'due_amount' => $payload['payment_status'] === 'paid' ? 0 : $sale->due_amount,
                ]);
            });
        } catch (RuntimeException $e) {
            // Reopening needs the goods back off the shelf; if they have since
            // been sold, leave the order cancelled rather than oversell.
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Order status updated successfully.');
    }

    /**
     * Stamped the first time an order reaches a status whose money has been
     * received, and cleared when it is moved back out of one so the revenue
     * reports follow the correction.
     */
    private function deliveredAtFor(Order $sale, string $status): ?Carbon
    {
        if (! in_array($status, Order::REVENUE_STATUSES, true)) {
            return null;
        }

        return $sale->delivered_at ?? now();
    }

    public function ship(Request $request, $id)
    {
        $payload = $request->validate([
            'courier' => ['required', 'in:pathao,steadfast'],
            'city_id' => ['required_if:courier,pathao', 'nullable', 'integer'],
            'zone_id' => ['required_if:courier,pathao', 'nullable', 'integer'],
            'area_id' => ['required_if:courier,pathao', 'nullable', 'integer'],
        ]);

        $sale = Order::with('shipments')->findOrFail($id);
        $settings = WebSetting::first() ?? new WebSetting();

        // A double click, a back-and-resubmit or a stale tab would otherwise raise
        // a second parcel against the same order -- two deliveries, two charges.
        if ($live = $sale->liveShipment()) {
            return redirect()->back()->with('error', 'This order is already booked with ' . ucfirst($live->courier)
                . ' (' . $live->consignment_id . '). Cancel that consignment at the courier before booking again.');
        }

        // Both couriers reject a malformed mobile, and a rejected booking still
        // costs a call and leaves a dead shipment row -- so it is caught here first.
        if (! $sale->hasDeliverablePhone()) {
            return redirect()->back()->with('error', 'The delivery phone (' . ($sale->customer_phone ?: 'none on file')
                . ') is not a valid Bangladeshi mobile number. Couriers need 11 digits in the form 01XXXXXXXXX.');
        }

        if ($payload['courier'] === 'pathao') {
            $service = new PathaoCourierService($settings);

            if (! $service->enabled()) {
                return redirect()->back()->with('error', 'Pathao courier is not configured yet. Add your credentials in Settings > Courier.');
            }

            $shipment = $service->createOrder($sale, [
                'city_id' => (int) $payload['city_id'],
                'zone_id' => (int) $payload['zone_id'],
                'area_id' => (int) $payload['area_id'],
            ]);
        } else {
            $service = new SteadfastCourierService($settings);

            if (! $service->enabled()) {
                return redirect()->back()->with('error', 'Steadfast courier is not configured yet. Add your credentials in Settings > Courier.');
            }

            $shipment = $service->createOrder($sale);
        }

        // A refusal comes back looking like a success -- no consignment id is the
        // only thing that says the parcel was never actually accepted.
        if (blank($shipment->consignment_id)) {
            return redirect()->back()->with('error', ucfirst($shipment->courier) . ' refused the booking: ' . $shipment->failureReason());
        }

        return redirect()->back()->with('success', 'Shipment booked with ' . $shipment->consignment_id . '.');
    }

    /**
     * Re-read a consignment status from the courier.
     *
     * Neither courier calls back, and neither merchant API can cancel or
     * re-address a parcel -- an operator who booked a wrong address cancels it
     * in the courier panel. This is what lets the shop find that out: once the
     * pulled status is a closed one, the order stops counting as live and the
     * booking form comes back so it can be re-sent correctly.
     */
    public function syncShipment($saleId, $shipmentId)
    {
        $shipment = Shipment::where('order_id', $saleId)->findOrFail($shipmentId);
        $settings = WebSetting::first() ?? new WebSetting();

        if (blank($shipment->consignment_id)) {
            return redirect()->back()->with('error', 'That booking never reached the courier, so there is no status to pull.');
        }

        if ($shipment->courier === 'pathao') {
            $service = new PathaoCourierService($settings);

            if (! $service->enabled()) {
                return redirect()->back()->with('error', 'Pathao courier is not configured yet.');
            }

            $info = $service->orderInfo($shipment->consignment_id);
            $status = $info['order_status'] ?? null;
        } else {
            $service = new SteadfastCourierService($settings);

            if (! $service->enabled()) {
                return redirect()->back()->with('error', 'Steadfast courier is not configured yet.');
            }

            $info = $service->getStatus($shipment->consignment_id);
            $status = $info['delivery_status'] ?? null;
        }

        if (blank($status)) {
            return redirect()->back()->with('error', 'The courier returned no status for ' . $shipment->consignment_id . '.');
        }

        // Merged, not replaced: the booking response carries the delivery fee and
        // a status pull does not, so overwriting would quietly lose it.
        $shipment->update([
            'status' => $status,
            'raw_response' => array_merge($shipment->raw_response ?? [], $info),
        ]);

        // Say what the status means for the order, not just what it is: an
        // operator pressing Sync is usually asking whether they can re-book yet.
        $message = $shipment->fresh()->isLive()
            ? ucfirst($shipment->courier) . ' says ' . $shipment->statusLabel() . ' -- the parcel is still live, so re-booking stays locked.'
            : ucfirst($shipment->courier) . ' says ' . $shipment->statusLabel() . ' -- this order is free to book again.';

        return redirect()->back()->with('success', $message);
    }

    /**
     * The city list, plus which of them the named order already points at.
     *
     * The suggestion rides along with the list rather than the Show payload so
     * that resolving it -- which costs a Pathao call -- never sits between the
     * operator and the order page rendering.
     */
    public function pathaoCities(Request $request)
    {
        $settings = WebSetting::first() ?? new WebSetting();
        $service = new PathaoCourierService($settings);

        if (! $service->enabled()) {
            return response()->json(['data' => [], 'suggested_city_id' => null]);
        }

        $district = $request->filled('order')
            ? Order::with('district')->find($request->query('order'))?->district?->name
            : null;

        return response()->json([
            'data' => $service->listCities(),
            'suggested_city_id' => $service->resolveCityId($district),
        ]);
    }

    public function pathaoZones($cityId)
    {
        $settings = WebSetting::first() ?? new WebSetting();
        $service = new PathaoCourierService($settings);

        return response()->json($service->enabled() ? $service->listZones((int) $cityId) : []);
    }

    public function pathaoAreas($zoneId)
    {
        $settings = WebSetting::first() ?? new WebSetting();
        $service = new PathaoCourierService($settings);

        return response()->json($service->enabled() ? $service->listAreas((int) $zoneId) : []);
    }
}
