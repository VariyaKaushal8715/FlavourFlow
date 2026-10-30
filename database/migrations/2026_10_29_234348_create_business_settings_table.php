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
        Schema::create('business_settings', function (Blueprint $table) {
            $table->id();
            $table->string('store_name', 150)->default('FlavourFlow');
            $table->string('legal_name', 200)->nullable();
            $table->string('logo_path')->nullable();
            $table->text('business_address')->nullable();
            $table->text('billing_address')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('website_url', 200)->nullable();
            $table->string('gstin', 30)->nullable();
            $table->string('invoice_prefix', 10)->default('INV');
            $table->unsignedBigInteger('next_invoice_number')->default(1001);
            $table->decimal('tax_rate', 5, 2)->default(0.00);
            $table->string('tax_label', 50)->default('GST');
            $table->string('tax_mode', 20)->default('inclusive');
            $table->text('invoice_footer')->nullable();
            $table->text('payment_instructions')->nullable();
            $table->text('return_policy_text')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_settings');
    }
};
