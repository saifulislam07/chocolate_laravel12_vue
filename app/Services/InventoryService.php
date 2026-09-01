<?php

namespace App\Services;

use App\Models\Order;
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

    /**
     * Put an order's goods back on the shelf, for a cancellation or a deletion.
     *
     * Quantities already sent back through a sales return are skipped: that
     * stock was restocked when the return was processed, so counting it again
     * here would invent inventory that never came back.
     */
    public function releaseOrderStock(Order $order, string $note): void
    {
        DB::transaction(function () use ($order, $note): void {
            foreach ($this->outstandingLines($order) as [$product, $quantity]) {
                $this->adjust($product, $quantity, 'cancel_in', $order, $note);
            }
        });
    }

    /**
     * Take an order's goods back off the shelf — the mirror of a release, for
     * when a cancelled order is reopened. Throws if the stock is no longer
     * there, which leaves the order cancelled rather than overselling.
     */
    public function reserveOrderStock(Order $order, string $note): void
    {
        DB::transaction(function () use ($order, $note): void {
            foreach ($this->outstandingLines($order) as [$product, $quantity]) {
                $this->adjust($product, -$quantity, 'sale_out', $order, $note);
            }
        });
    }

    /**
     * The order's lines that still hold stock, as [product, quantity] pairs.
     *
     * @return list<array{0: Product, 1: int}>
     */
    private function outstandingLines(Order $order): array
    {
        $lines = [];

        foreach ($order->items as $item) {
            $quantity = (int) $item->quantity - (int) $item->returned_quantity;

            if ($quantity > 0 && $item->product) {
                $lines[] = [$item->product, $quantity];
            }
        }

        return $lines;
    }
}
