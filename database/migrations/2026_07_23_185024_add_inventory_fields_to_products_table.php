<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('sku')->nullable()->unique()->after('slug');
            $table->string('unit')->default('100 g')->after('category');
            $table->decimal('compare_at_price', 10, 2)->nullable()->after('price');
            $table->unsignedInteger('quantity')->default(0)->after('compare_at_price');
            $table->unsignedInteger('low_stock_threshold')->default(5)->after('quantity');

            $table->index(['is_active', 'quantity']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'quantity']);
            $table->dropUnique(['sku']);
            $table->dropColumn([
                'sku',
                'unit',
                'compare_at_price',
                'quantity',
                'low_stock_threshold',
            ]);
        });
    }
};
