<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\WebSetting;
use App\Services\Courier\PathaoCourierService;
use App\Services\Courier\SteadfastCourierService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SaleController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Sales/Index', [
            'sales' => Order::with(['customer', 'user'])->latest()->get()
        ]);
    }

    public function show($id)
    {
        $sale = Order::with(['customer', 'user', 'items.product', 'shipments'])->findOrFail($id);
        $settings = WebSetting::first();

        return Inertia::render('Admin/Sales/Show', [
            'sale' => $sale,
            'courierOptions' => [
                'pathao' => (new PathaoCourierService($settings ?? new WebSetting()))->enabled(),
                'steadfast' => (new SteadfastCourierService($settings ?? new WebSetting()))->enabled(),
            ],
        ]);
    }

    public function destroy($id)
    {
        $sale = Order::findOrFail($id);
        $sale->delete();
        return redirect()->route('admin.sales.index')->with('success', 'Sale record deleted successfully.');
    }

    public function updateStatus(Request $request, $id)
    {
        $payload = $request->validate([
            'status' => ['required', 'in:pending,processing,shipped,delivered,cancelled,partially_returned,returned'],
            'payment_status' => ['required', 'in:unpaid,partial,paid'],
        ]);

        $sale = Order::findOrFail($id);

        $sale->update([
            'status' => $payload['status'],
            'payment_status' => $payload['payment_status'],
            'paid_amount' => $payload['payment_status'] === 'paid' ? $sale->total : $sale->paid_amount,
            'due_amount' => $payload['payment_status'] === 'paid' ? 0 : $sale->due_amount,
        ]);

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
