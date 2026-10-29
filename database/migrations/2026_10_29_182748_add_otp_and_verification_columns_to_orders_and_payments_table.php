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
            $table->string('order_confirmation_otp', 10)->nullable()->after('payment_status');
            $table->timestamp('otp_expires_at')->nullable()->after('order_confirmation_otp');
            $table->timestamp('otp_sent_at')->nullable()->after('otp_expires_at');
            $table->timestamp('otp_verified_at')->nullable()->after('otp_sent_at');
            $table->timestamp('payment_verified_at')->nullable()->after('otp_verified_at');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('verification_status', 30)->nullable()->default('pending')->after('status');
            $table->unsignedInteger('verification_attempts')->default(0)->after('verification_status');
            $table->timestamp('verified_at')->nullable()->after('verification_attempts');
            $table->string('customer_utr', 100)->nullable()->after('verified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'order_confirmation_otp',
                'otp_expires_at',
                'otp_sent_at',
                'otp_verified_at',
                'payment_verified_at',
            ]);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'verification_status',
                'verification_attempts',
                'verified_at',
                'customer_utr',
            ]);
        });
    }
};
