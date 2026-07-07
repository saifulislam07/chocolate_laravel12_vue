<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InventoryService
{
    /**
     * Apply a signed stock change to a product and record it in the audit ledger.
     * Positive $delta increases stock (purchase/return), negative decreases it (sale).
     */
    public function adjust(Product $product, int $delta, string $type, ?Model $reference = null, ?string $note = null): StockMovement
    {
        return DB::transaction(function () use ($product, $delta, $type, $reference, $note) {
            $locked = Product::whereKey($product->id)->lockForUpdate()->firstOrFail();
            $newStock = $locked->stock + $delta;

            if ($newStock < 0) {
                throw new RuntimeException("Insufficient stock for \"{$locked->name}\": have {$locked->stock}, need " . abs($delta) . '.');
            }

            $locked->update(['stock' => $newStock]);
            $product->stock = $newStock;

            return StockMovement::create([
                'product_id' => $locked->id,
                'type' => $type,
                'quantity' => $delta,
                'resulting_stock' => $newStock,
                'reference_type' => $reference ? $reference::class : null,
                'reference_id' => $reference?->id,
                'note' => $note,
                'created_by' => Auth::id(),
            ]);
        });
    }

    /**
     * Check whether a product has enough stock to satisfy a requested quantity,
     * without mutating anything — used for pre-validation before building an order.
     */
    public function hasSufficientStock(Product $product, int $quantity): bool
    {
        return $product->stock >= $quantity;
    }
}
