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
        Schema::table('products', function (Blueprint $table) {
            $table->text('long_description')->nullable()->after('description');
            $table->json('highlights')->nullable()->after('long_description');
            $table->text('ingredients')->nullable()->after('highlights');
            $table->text('usage_instructions')->nullable()->after('ingredients');
            $table->string('origin')->nullable()->after('usage_instructions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'long_description',
                'highlights',
                'ingredients',
                'usage_instructions',
                'origin',
            ]);
        });
    }
};
