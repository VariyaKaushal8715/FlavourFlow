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
        Schema::table('cart_items', function (Blueprint $table) {
            if (! Schema::hasColumn('cart_items', 'product_name')) {
                $table->string('product_name')->after('product_id');
            }

            if (! Schema::hasColumn('cart_items', 'product_slug')) {
                $table->string('product_slug')->nullable()->after('product_name');
            }

            if (! Schema::hasColumn('cart_items', 'sku')) {
                $table->string('sku')->nullable()->after('product_slug');
            }

            if (! Schema::hasColumn('cart_items', 'category')) {
                $table->string('category')->nullable()->after('sku');
            }

            if (! Schema::hasColumn('cart_items', 'unit')) {
                $table->string('unit')->nullable()->after('category');
            }

            if (! Schema::hasColumn('cart_items', 'unit_price')) {
                $table->decimal('unit_price', 10, 2)->default(0)->after('quantity');
            }

            if (! Schema::hasColumn('cart_items', 'line_total')) {
                $table->decimal('line_total', 10, 2)->default(0)->after('unit_price');
            }

            if (! Schema::hasColumn('cart_items', 'image_path')) {
                $table->string('image_path')->nullable()->after('line_total');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $columns = array_filter([
                Schema::hasColumn('cart_items', 'image_path') ? 'image_path' : null,
                Schema::hasColumn('cart_items', 'line_total') ? 'line_total' : null,
                Schema::hasColumn('cart_items', 'unit_price') ? 'unit_price' : null,
                Schema::hasColumn('cart_items', 'unit') ? 'unit' : null,
                Schema::hasColumn('cart_items', 'category') ? 'category' : null,
                Schema::hasColumn('cart_items', 'sku') ? 'sku' : null,
                Schema::hasColumn('cart_items', 'product_slug') ? 'product_slug' : null,
                Schema::hasColumn('cart_items', 'product_name') ? 'product_name' : null,
            ]);

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
