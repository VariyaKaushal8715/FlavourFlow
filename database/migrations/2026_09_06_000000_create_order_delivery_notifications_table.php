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
        if (! Schema::hasTable('order_delivery_notifications')) {
            Schema::create('order_delivery_notifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
                $table->string('channel'); // customer_whatsapp, customer_email, admin_whatsapp, admin_email
                $table->string('type')->default('order_confirmed'); // order_confirmed, etc.
                $table->string('recipient');
                $table->string('status')->default('pending'); // pending, sent, failed, skipped
                $table->text('error_message')->nullable();
                $table->text('payload')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamps();

                $table->unique(['order_id', 'type', 'channel'], 'order_type_channel_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_delivery_notifications');
    }
};
