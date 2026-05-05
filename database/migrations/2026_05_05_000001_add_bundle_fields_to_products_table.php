<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_bundle')->default(false)->after('is_new');
            $table->text('bundle_note')->nullable()->after('is_bundle');
            $table->string('bundle_discount_type')->nullable()->after('bundle_note');
            $table->decimal('bundle_discount_value', 15, 2)->default(0)->after('bundle_discount_type');
        });

        Schema::create('bundle_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bundle_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->timestamps();

            $table->unique(['bundle_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bundle_product');

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'is_bundle',
                'bundle_note',
                'bundle_discount_type',
                'bundle_discount_value',
            ]);
        });
    }
};
