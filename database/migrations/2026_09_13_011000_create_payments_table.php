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
        if (! Schema::hasTable('payments')) {
            return;
        }

        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'provider')) {
                $table->string('provider')->default('demo')->after('payment_method');
            }

            if (! Schema::hasColumn('payments', 'transaction_id')) {
                $table->string('transaction_id')->nullable()->unique()->after('provider');
            }

            if (! Schema::hasColumn('payments', 'gateway_order_id')) {
                $table->string('gateway_order_id')->nullable()->after('transaction_id');
            }

            if (! Schema::hasColumn('payments', 'payment_details')) {
                $table->json('payment_details')->nullable()->after('response_data');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            foreach (['payment_details', 'gateway_order_id', 'transaction_id', 'provider'] as $column) {
                if (Schema::hasColumn('payments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
