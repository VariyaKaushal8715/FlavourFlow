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
        Schema::create('coupon_reward_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('min_purchase_amount', 10, 2);
            $table->string('discount_type')->default('percent'); // 'percent' or 'fixed'
            $table->decimal('discount_value', 10, 2);
            $table->decimal('max_discount', 10, 2)->nullable();
            $table->decimal('min_next_order_amount', 10, 2)->default(0.00);
            $table->unsignedInteger('validity_days')->default(30);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupon_reward_rules');
    }
};
