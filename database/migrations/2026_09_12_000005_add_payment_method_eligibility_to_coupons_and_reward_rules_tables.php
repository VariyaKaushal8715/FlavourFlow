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
        Schema::table('coupons', function (Blueprint $table) {
            $table->string('payment_method_eligibility')->default('both')->after('discount_value'); // 'both', 'online', 'cod'
        });

        Schema::table('coupon_reward_rules', function (Blueprint $table) {
            $table->string('payment_method_eligibility')->default('online')->after('discount_value'); // 'both', 'online', 'cod'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn('payment_method_eligibility');
        });

        Schema::table('coupon_reward_rules', function (Blueprint $table) {
            $table->dropColumn('payment_method_eligibility');
        });
    }
};
