<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class BundleController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Bundles/Index', [
            'bundles' => Product::query()
                ->with(['images', 'bundleItems.images'])
                ->where('is_bundle', true)
                ->latest()
                ->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Bundles/Create', [
            'products' => $this->availableProducts(),
        ]);
    }

    public function store(Request $request)
    {
        $payload = $this->validatedPayload($request);

        DB::transaction(function () use ($request, $payload) {
            $pricing = $this->calculatePricing($payload['items'], $payload['discount_type'], $payload['discount_value']);

            $bundle = Product::create([
                'name' => $payload['name'],
                'slug' => Str::slug($payload['name']) . '-' . uniqid(),
                'description' => $payload['description'] ?? null,
                'cost_price' => 0,
                'price' => $pricing['price'],
                'compare_at_price' => $pricing['compare_at_price'],
                'stock' => $payload['stock'],
                'alert_quantity' => $payload['alert_quantity'] ?? 10,
                'sku' => $payload['sku'] ?? null,
                'is_active' => $request->boolean('is_active', true),
                'is_featured' => $request->boolean('is_featured'),
                'is_new' => $request->boolean('is_new'),
                'is_bundle' => true,
                'bundle_note' => $payload['bundle_note'] ?? null,
                'bundle_discount_type' => $payload['discount_type'],
                'bundle_discount_value' => $payload['discount_value'] ?? 0,
            ]);

            $this->syncBundleItems($bundle, $payload['items']);
            $this->storeImages($request, $bundle);
        });

        return redirect()->route('admin.bundles.index')->with('success', 'Bundle created successfully.');
    }

    public function edit(Product $bundle)
    {
        abort_unless($bundle->is_bundle, 404);

        $bundle->load(['images', 'bundleItems.images']);

        return Inertia::render('Admin/Bundles/Edit', [
            'bundle' => $bundle,
            'products' => $this->availableProducts(),
        ]);
    }

    public function update(Request $request, Product $bundle)
    {
        abort_unless($bundle->is_bundle, 404);

        $payload = $this->validatedPayload($request, $bundle->id);

        DB::transaction(function () use ($request, $bundle, $payload) {
            $pricing = $this->calculatePricing($payload['items'], $payload['discount_type'], $payload['discount_value']);

            $bundle->update([
                'name' => $payload['name'],
                'description' => $payload['description'] ?? null,
                'price' => $pricing['price'],
                'compare_at_price' => $pricing['compare_at_price'],
                'stock' => $payload['stock'],
                'alert_quantity' => $payload['alert_quantity'] ?? 10,
                'sku' => $payload['sku'] ?? null,
                'is_active' => $request->boolean('is_active'),
                'is_featured' => $request->boolean('is_featured'),
                'is_new' => $request->boolean('is_new'),
                'bundle_note' => $payload['bundle_note'] ?? null,
                'bundle_discount_type' => $payload['discount_type'],
                'bundle_discount_value' => $payload['discount_value'] ?? 0,
            ]);

            $this->syncBundleItems($bundle, $payload['items']);
            $this->storeImages($request, $bundle);
        });

        return redirect()->route('admin.bundles.index')->with('success', 'Bundle updated successfully.');
    }

    public function destroy(Product $bundle)
    {
        abort_unless($bundle->is_bundle, 404);

        foreach ($bundle->images as $image) {
            Storage::disk('uploads')->delete(str_replace('/uploads/', '', $image->image_path));
        }

        $bundle->delete();

        return redirect()->back()->with('success', 'Bundle deleted successfully.');
    }

    private function validatedPayload(Request $request, ?int $bundleId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:100', 'unique:products,sku,' . $bundleId],
            'description' => ['nullable', 'string'],
            'bundle_note' => ['nullable', 'string'],
            'discount_type' => ['nullable', 'in:fixed,percent'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'alert_quantity' => ['nullable', 'integer', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'images.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);
    }

    private function calculatePricing(array $items, ?string $discountType, mixed $discountValue): array
    {
        $products = Product::whereIn('id', collect($items)->pluck('product_id'))->get()->keyBy('id');
        $subtotal = collect($items)->sum(fn ($item) => (float) $products[$item['product_id']]->price * (int) $item['quantity']);
        $discountValue = (float) ($discountValue ?? 0);
        $discount = $discountType === 'percent' ? ($subtotal * min($discountValue, 100) / 100) : $discountValue;
        $price = max($subtotal - $discount, 0);

        return [
            'price' => $price,
            'compare_at_price' => $subtotal > $price ? $subtotal : null,
        ];
    }

    private function syncBundleItems(Product $bundle, array $items): void
    {
        $syncData = collect($items)
            ->mapWithKeys(fn ($item) => [$item['product_id'] => ['quantity' => $item['quantity']]])
            ->all();

        $bundle->bundleItems()->sync($syncData);
    }

    private function storeImages(Request $request, Product $bundle): void
    {
        if (!$request->hasFile('images')) {
            return;
        }

        foreach ($request->file('images') as $file) {
            $path = $file->store('products', 'uploads');
            $bundle->images()->create([
                'image_path' => '/uploads/' . $path,
                'is_primary' => $bundle->images()->count() === 0,
            ]);
        }
    }

    private function availableProducts()
    {
        return Product::query()
            ->with('images')
            ->where('is_active', true)
            ->where('is_bundle', false)
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'sku']);
    }
}
