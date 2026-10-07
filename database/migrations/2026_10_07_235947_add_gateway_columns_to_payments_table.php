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
        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'razorpay_order_id')) {
                $table->string('razorpay_order_id')->nullable()->index();
            }
            if (! Schema::hasColumn('payments', 'razorpay_payment_id')) {
                $table->string('razorpay_payment_id')->nullable()->index();
            }
            if (! Schema::hasColumn('payments', 'razorpay_signature')) {
                $table->string('razorpay_signature')->nullable();
            }
            if (! Schema::hasColumn('payments', 'cashfree_order_id')) {
                $table->string('cashfree_order_id')->nullable()->index();
            }
            if (! Schema::hasColumn('payments', 'cashfree_payment_id')) {
                $table->string('cashfree_payment_id')->nullable()->index();
            }
            if (! Schema::hasColumn('payments', 'payment_session_id')) {
                $table->string('payment_session_id')->nullable();
            }
            if (! Schema::hasColumn('payments', 'error_message')) {
                $table->text('error_message')->nullable();
            }
            if (! Schema::hasColumn('payments', 'cashfree_refund_id')) {
                $table->string('cashfree_refund_id')->nullable();
            }
            if (! Schema::hasColumn('payments', 'refund_status')) {
                $table->string('refund_status')->nullable();
            }
            if (! Schema::hasColumn('payments', 'refunded_amount')) {
                $table->decimal('refunded_amount', 10, 2)->nullable();
            }
            if (! Schema::hasColumn('payments', 'response_data')) {
                $table->json('response_data')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $columns = [
                'razorpay_order_id',
                'razorpay_payment_id',
                'razorpay_signature',
                'cashfree_order_id',
                'cashfree_payment_id',
                'payment_session_id',
                'error_message',
                'cashfree_refund_id',
                'refund_status',
                'refunded_amount',
                'response_data',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('payments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
