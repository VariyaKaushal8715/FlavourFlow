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
            $table->string('name')->nullable()->after('order_number');
            $table->string('mobile')->nullable()->after('name');
            $table->string('email')->nullable()->after('mobile');
            $table->text('address')->nullable()->after('email');
            $table->string('city')->nullable()->after('address');
            $table->string('state')->nullable()->after('city');
            $table->string('pincode')->nullable()->after('state');
            $table->string('country')->nullable()->after('pincode');
            $table->string('payment_method')->nullable()->after('country');
            $table->decimal('subtotal', 10, 2)->nullable()->after('payment_method');
            $table->decimal('delivery_charge', 10, 2)->nullable()->after('subtotal');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->string('product_name')->nullable()->after('product_id');
            $table->string('product_slug')->nullable()->after('product_name');
            $table->string('sku')->nullable()->after('product_slug');
            $table->string('unit')->nullable()->after('sku');
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
