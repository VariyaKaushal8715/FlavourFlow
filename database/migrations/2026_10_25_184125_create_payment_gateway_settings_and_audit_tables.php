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
        if (! Schema::hasTable('payment_gateway_settings')) {
            Schema::create('payment_gateway_settings', function (Blueprint $table) {
                $table->id();
                $table->string('active_gateway')->default('demo'); // demo, cashfree, razorpay
                $table->string('environment')->default('sandbox'); // sandbox, live
                $table->string('razorpay_key_id')->nullable();
                $table->text('razorpay_key_secret')->nullable(); // encrypted
                $table->text('razorpay_webhook_secret')->nullable(); // encrypted
                $table->string('cashfree_app_id')->nullable();
                $table->text('cashfree_secret_key')->nullable(); // encrypted
                $table->string('cashfree_api_version')->default('2023-08-01');
                $table->timestamps();
            });
        } else {
            if (! Schema::hasColumn('payment_gateway_settings', 'cashfree_api_version')) {
                Schema::table('payment_gateway_settings', function (Blueprint $table) {
                    $table->string('cashfree_api_version')->default('2023-08-01')->after('cashfree_secret_key');
                });
            }
            if (! Schema::hasColumn('payment_gateway_settings', 'razorpay_webhook_secret')) {
                Schema::table('payment_gateway_settings', function (Blueprint $table) {
                    $table->text('razorpay_webhook_secret')->nullable()->after('razorpay_key_secret');
                });
            }
        }

        if (! Schema::hasTable('payment_audit_logs')) {
            Schema::create('payment_audit_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('action'); // e.g. gateway_updated, credentials_updated, live_mode_toggled
                $table->string('gateway')->nullable();
                $table->string('environment')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->json('details')->nullable(); // sanitized metadata, never stores secrets
                $table->timestamps();
            });
        } elseif (! Schema::hasColumn('payment_audit_logs', 'environment')) {
            Schema::table('payment_audit_logs', function (Blueprint $table) {
                $table->string('environment')->nullable()->after('gateway');
            });
        }

        if (Schema::hasTable('payments')) {
            if (! Schema::hasColumn('payments', 'is_demo')) {
                Schema::table('payments', function (Blueprint $table) {
                    $table->boolean('is_demo')->default(false)->after('status')->index();
                });
            }
            if (! Schema::hasColumn('payments', 'razorpay_refund_id')) {
                Schema::table('payments', function (Blueprint $table) {
                    $table->string('razorpay_refund_id')->nullable()->after('razorpay_payment_id');
                });
            }
        }

        if (Schema::hasTable('orders') && ! Schema::hasColumn('orders', 'is_demo')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->boolean('is_demo')->default(false)->after('status')->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'is_demo')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('is_demo');
            });
        }

        if (Schema::hasTable('payments') && Schema::hasColumn('payments', 'is_demo')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropColumn('is_demo');
            });
        }

        Schema::dropIfExists('payment_audit_logs');
        Schema::dropIfExists('payment_gateway_settings');
    }
};
