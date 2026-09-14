<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_sort_options', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->string('sort_field')->default('is_featured');
            $table->string('sort_direction')->default('desc');
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        $now = now();
        $defaults = [
            [
                'key' => 'featured',
                'label' => 'Featured',
                'sort_field' => 'is_featured',
                'sort_direction' => 'desc',
                'display_order' => 1,
                'is_active' => true,
                'is_default' => true,
                'is_system' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'rating',
                'label' => 'Top Rated',
                'sort_field' => 'rating',
                'sort_direction' => 'desc',
                'display_order' => 2,
                'is_active' => true,
                'is_default' => false,
                'is_system' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'price_asc',
                'label' => 'Price: Low to High',
                'sort_field' => 'price',
                'sort_direction' => 'asc',
                'display_order' => 3,
                'is_active' => true,
                'is_default' => false,
                'is_system' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'price_desc',
                'label' => 'Price: High to Low',
                'sort_field' => 'price',
                'sort_direction' => 'desc',
                'display_order' => 4,
                'is_active' => true,
                'is_default' => false,
                'is_system' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'newest',
                'label' => 'Newest Arrivals',
                'sort_field' => 'created_at',
                'sort_direction' => 'desc',
                'display_order' => 5,
                'is_active' => true,
                'is_default' => false,
                'is_system' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'best_selling',
                'label' => 'Best Selling',
                'sort_field' => 'best_selling',
                'sort_direction' => 'desc',
                'display_order' => 6,
                'is_active' => true,
                'is_default' => false,
                'is_system' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'name',
                'label' => 'Name: A to Z',
                'sort_field' => 'name',
                'sort_direction' => 'asc',
                'display_order' => 7,
                'is_active' => true,
                'is_default' => false,
                'is_system' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'discount',
                'label' => 'Biggest Discounts',
                'sort_field' => 'discount',
                'sort_direction' => 'desc',
                'display_order' => 8,
                'is_active' => true,
                'is_default' => false,
                'is_system' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'price_range',
                'label' => 'Price Range Filter',
                'sort_field' => 'price',
                'sort_direction' => 'asc',
                'display_order' => 9,
                'is_active' => true,
                'is_default' => false,
                'is_system' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('product_sort_options')->insert($defaults);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_sort_options');
    }
};
