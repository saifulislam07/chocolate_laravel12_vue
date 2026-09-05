<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\WebSetting;
use App\Services\Courier\PathaoCourierService;
use App\Services\Courier\SteadfastCourierService;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use RuntimeException;

class SaleController extends Controller
{
    public function index()
    {
        // shipments feeds the shipping_status accessor; appending both here
        // keeps the list sortable on plain strings rather than nested relations.
        $sales = Order::with(['customer', 'user', 'shipments'])->latest()->get();
        $sales->each->append(['source_label', 'shipping_status']);

        return Inertia::render('Admin/Sales/Index', [
            'sales' => $sales,
        ]);
    }

    public function show($id)
    {
        $sale = Order::with(['customer', 'user', 'items.product', 'shipments'])->findOrFail($id);
        $sale->append('total_in_words');
        $settings = WebSetting::first();

        return Inertia::render('Admin/Sales/Show', [
            'sale' => $sale,
            'courierOptions' => [
                'pathao' => (new PathaoCourierService($settings ?? new WebSetting()))->enabled(),
                'steadfast' => (new SteadfastCourierService($settings ?? new WebSetting()))->enabled(),
            ],
        ]);
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
            'status' => ['required', 'in:pending,processing,shipped,delivered,cancelled,partially_returned,returned'],
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

    public function ship(Request $request, $id)
    {
        $payload = $request->validate([
            'courier' => ['required', 'in:pathao,steadfast'],
            'city_id' => ['required_if:courier,pathao', 'nullable', 'integer'],
            'zone_id' => ['required_if:courier,pathao', 'nullable', 'integer'],
            'area_id' => ['required_if:courier,pathao', 'nullable', 'integer'],
        ]);

        $sale = Order::findOrFail($id);
        $settings = WebSetting::first() ?? new WebSetting();

        if ($payload['courier'] === 'pathao') {
            $service = new PathaoCourierService($settings);

            if (! $service->enabled()) {
                return redirect()->back()->with('error', 'Pathao courier is not configured yet. Add your credentials in Settings > Courier.');
            }

            $service->createOrder($sale, [
                'city_id' => (int) $payload['city_id'],
                'zone_id' => (int) $payload['zone_id'],
                'area_id' => (int) $payload['area_id'],
            ]);
        } else {
            $service = new SteadfastCourierService($settings);

            if (! $service->enabled()) {
                return redirect()->back()->with('error', 'Steadfast courier is not configured yet. Add your credentials in Settings > Courier.');
            }

            $service->createOrder($sale);
        }

        return redirect()->back()->with('success', 'Shipment booked successfully.');
    }

    public function pathaoCities()
    {
        $settings = WebSetting::first() ?? new WebSetting();
        $service = new PathaoCourierService($settings);

        return response()->json($service->enabled() ? $service->listCities() : []);
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
