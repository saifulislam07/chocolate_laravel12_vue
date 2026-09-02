<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Where the sale came from — the Facebook page, a WhatsApp message, a phone
     * call. Recorded by the operator at POS so the marketing spend can be read
     * back against the orders it actually produced.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->string('lead_source')->nullable()->after('order_source');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn('lead_source');
        });
    }
};
