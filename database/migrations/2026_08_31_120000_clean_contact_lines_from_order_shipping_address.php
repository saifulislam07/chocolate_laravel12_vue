<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Older web orders stored the customer name, phone and email baked into
     * shipping_address, which the invoice then rendered a second time from the
     * dedicated columns. Strip those lines so only the street address remains.
     */
    public function up(): void
    {
        DB::table('orders')
            ->select('id', 'shipping_address', 'customer_name')
            ->whereNotNull('shipping_address')
            ->orderBy('id')
            ->chunkById(200, function ($orders) {
                foreach ($orders as $order) {
                    $lines = preg_split('/\r\n|\r|\n/', (string) $order->shipping_address);

                    $cleaned = collect($lines)
                        ->map(fn ($line) => trim($line))
                        ->reject(fn ($line) => $line === '')
                        ->reject(fn ($line) => preg_match('/^(phone|email|mobile)\s*:/i', $line))
                        ->reject(fn ($line) => $order->customer_name && strcasecmp($line, trim($order->customer_name)) === 0)
                        ->implode("\n");

                    if ($cleaned !== $order->shipping_address) {
                        DB::table('orders')->where('id', $order->id)->update([
                            'shipping_address' => $cleaned,
                        ]);
                    }
                }
            });
    }

    public function down(): void
    {
        // Irreversible data cleanup.
    }
};
