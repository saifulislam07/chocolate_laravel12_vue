<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Revenue lands in the account on delivery, not on order date, so
            // reports need the moment the money was actually handed over.
            $table->timestamp('delivered_at')->nullable()->after('status');
        });

        // Orders that were already delivered before this column existed have no
        // real timestamp to recover; the last status change is the closest one.
        DB::table('orders')
            ->whereIn('status', ['delivered', 'completed', 'partially_returned', 'returned'])
            ->update(['delivered_at' => DB::raw('updated_at')]);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('delivered_at');
        });
    }
};
