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
            if (! Schema::hasColumn('orders', 'delivery_option')) {
                $table->string('delivery_option')->default('standard')->nullable()->after('payment_method');
            }
            if (! Schema::hasColumn('orders', 'delivery_days')) {
                $table->string('delivery_days')->default('4-5 days')->nullable()->after('delivery_option');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['delivery_option', 'delivery_days']);
        });
    }
};
