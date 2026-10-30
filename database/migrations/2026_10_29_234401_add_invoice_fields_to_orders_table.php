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
            $table->string('invoice_number', 50)->nullable()->unique()->after('order_number');
            $table->timestamp('invoiced_at')->nullable()->after('invoice_number');
            $table->json('invoice_snapshot')->nullable()->after('invoiced_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['invoice_number', 'invoiced_at', 'invoice_snapshot']);
        });
    }
};
