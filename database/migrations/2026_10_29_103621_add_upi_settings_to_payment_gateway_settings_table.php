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
        if (Schema::hasTable('payment_gateway_settings')) {
            Schema::table('payment_gateway_settings', function (Blueprint $table) {
                if (! Schema::hasColumn('payment_gateway_settings', 'upi_enabled')) {
                    $table->boolean('upi_enabled')->default(true)->after('cashfree_api_version');
                }
                if (! Schema::hasColumn('payment_gateway_settings', 'upi_id')) {
                    $table->string('upi_id')->nullable()->after('upi_enabled');
                }
                if (! Schema::hasColumn('payment_gateway_settings', 'upi_name')) {
                    $table->string('upi_name')->nullable()->after('upi_id');
                }
                if (! Schema::hasColumn('payment_gateway_settings', 'upi_note')) {
                    $table->string('upi_note')->nullable()->after('upi_name');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('payment_gateway_settings')) {
            Schema::table('payment_gateway_settings', function (Blueprint $table) {
                $columns = ['upi_note', 'upi_name', 'upi_id', 'upi_enabled'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('payment_gateway_settings', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
