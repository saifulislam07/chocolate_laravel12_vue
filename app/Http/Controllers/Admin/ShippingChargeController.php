<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\Division;
use App\Models\WebSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ShippingChargeController extends Controller
{
    public function index()
    {
        $settings = WebSetting::first();

        return Inertia::render('Admin/Shipping/Index', [
            'settings' => [
                'default_shipping_charge' => (float) ($settings?->default_shipping_charge ?? 120),
                'free_shipping_threshold' => $settings?->free_shipping_threshold !== null
                    ? (float) $settings->free_shipping_threshold
                    : null,
            ],
            'divisions' => Division::with(['districts' => fn ($query) => $query->orderBy('name')])
                ->orderBy('name')
                ->get()
                ->map(fn (Division $division): array => [
                    'id' => $division->id,
                    'name' => $division->name,
                    'districts' => $division->districts->map(fn (District $district): array => [
                        'id' => $district->id,
                        'name' => $district->name,
                        'shipping_charge' => $district->shipping_charge !== null
                            ? (float) $district->shipping_charge
                            : null,
                    ])->values(),
                ]),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'default_shipping_charge' => 'required|numeric|min:0',
            'free_shipping_threshold' => 'nullable|numeric|min:0',
            'charges' => 'array',
            'charges.*.id' => 'required|exists:districts,id',
            'charges.*.shipping_charge' => 'nullable|numeric|min:0',
        ], [
            'charges.*.shipping_charge.numeric' => 'Every area charge must be a number.',
            'charges.*.shipping_charge.min' => 'Area charges cannot be negative.',
        ]);

        DB::transaction(function () use ($validated): void {
            $settings = WebSetting::first() ?? new WebSetting(['site_name' => config('app.name')]);
            $settings->default_shipping_charge = $validated['default_shipping_charge'];
            $settings->free_shipping_threshold = $validated['free_shipping_threshold'] ?? null;
            $settings->save();

            foreach ($validated['charges'] ?? [] as $row) {
                District::whereKey($row['id'])->update([
                    // Blank input means "fall back to the default charge".
                    'shipping_charge' => $row['shipping_charge'] === null || $row['shipping_charge'] === ''
                        ? null
                        : $row['shipping_charge'],
                ]);
            }
        });

        return redirect()->back()->with('success', 'Shipping charges updated successfully.');
    }

    public function storeArea(Request $request)
    {
        $validated = $request->validate([
            'division_id' => 'nullable|required_without:new_division|exists:divisions,id',
            'new_division' => 'nullable|required_without:division_id|string|max:255',
            'name' => 'required|string|max:255',
            'shipping_charge' => 'nullable|numeric|min:0',
        ], [
            'division_id.required_without' => 'Pick a division, or type a new one.',
            'new_division.required_without' => 'Pick a division, or type a new one.',
            'name.required' => 'Give the area a name.',
        ]);

        $division = filled($validated['new_division'] ?? null)
            ? Division::firstOrCreate(['name' => trim($validated['new_division'])])
            : Division::findOrFail($validated['division_id']);

        $name = trim($validated['name']);

        $exists = District::where('division_id', $division->id)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'name' => "\"{$name}\" already exists under {$division->name}.",
            ]);
        }

        District::create([
            'division_id' => $division->id,
            'name' => $name,
            'shipping_charge' => $validated['shipping_charge'] ?? null,
        ]);

        return redirect()->back()->with('success', "{$name} added under {$division->name}.");
    }
}
