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
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'name')) {
                $table->string('name')->nullable()->after('order_number');
            }
            if (!Schema::hasColumn('orders', 'mobile')) {
                $table->string('mobile')->nullable()->after('name');
            }
            if (!Schema::hasColumn('orders', 'email')) {
                $table->string('email')->nullable()->after('mobile');
            }
            if (!Schema::hasColumn('orders', 'address')) {
                $table->text('address')->nullable()->after('email');
            }
            if (!Schema::hasColumn('orders', 'city')) {
                $table->string('city')->nullable()->after('address');
            }
            if (!Schema::hasColumn('orders', 'state')) {
                $table->string('state')->nullable()->after('city');
            }
            if (!Schema::hasColumn('orders', 'pincode')) {
                $table->string('pincode')->nullable()->after('state');
            }
            if (!Schema::hasColumn('orders', 'country')) {
                $table->string('country')->nullable()->after('pincode');
            }
            if (!Schema::hasColumn('orders', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('country');
            }
            if (!Schema::hasColumn('orders', 'subtotal')) {
                $table->decimal('subtotal', 10, 2)->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('orders', 'delivery_charge')) {
                $table->decimal('delivery_charge', 10, 2)->nullable()->after('subtotal');
            }
        });

        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'product_name')) {
                $table->string('product_name')->nullable()->after('product_id');
            }
            if (!Schema::hasColumn('order_items', 'product_slug')) {
                $table->string('product_slug')->nullable()->after('product_name');
            }
            if (!Schema::hasColumn('order_items', 'sku')) {
                $table->string('sku')->nullable()->after('product_slug');
            }
            if (!Schema::hasColumn('order_items', 'unit')) {
                $table->string('unit')->nullable()->after('sku');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['product_name', 'product_slug', 'sku', 'unit']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'name',
                'mobile',
                'email',
                'address',
                'city',
                'state',
                'pincode',
                'country',
                'payment_method',
                'subtotal',
                'delivery_charge',
            ]);
        });
    }
};
