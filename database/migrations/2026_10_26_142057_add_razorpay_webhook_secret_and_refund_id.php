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
        if (Schema::hasTable('payment_gateway_settings') && ! Schema::hasColumn('payment_gateway_settings', 'razorpay_webhook_secret')) {
            Schema::table('payment_gateway_settings', function (Blueprint $table) {
                $table->text('razorpay_webhook_secret')->nullable()->after('razorpay_key_secret');
            });
        }

        if (Schema::hasTable('payments') && ! Schema::hasColumn('payments', 'razorpay_refund_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->string('razorpay_refund_id')->nullable()->after('razorpay_payment_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('payment_gateway_settings') && Schema::hasColumn('payment_gateway_settings', 'razorpay_webhook_secret')) {
            Schema::table('payment_gateway_settings', function (Blueprint $table) {
                $table->dropColumn('razorpay_webhook_secret');
            });
        }

        if (Schema::hasTable('payments') && Schema::hasColumn('payments', 'razorpay_refund_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropColumn('razorpay_refund_id');
            });
        }
    }
};
