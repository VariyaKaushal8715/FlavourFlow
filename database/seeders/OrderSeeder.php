<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::where('is_admin', false)->get();
        if ($users->isEmpty()) {
            $users = User::factory(10)->create();
        }

        $products = Product::all();
        if ($products->isEmpty()) {
            $products = Product::factory(10)->create();
        }

        $statuses = ['pending' => 10, 'processing' => 15, 'completed' => 70, 'cancelled' => 5];
        $paymentStatuses = ['pending' => 15, 'paid' => 80, 'failed' => 5];

        for ($i = 0; $i < 150; $i++) {
            $date = Carbon::now()->subDays(rand(0, 30))->subHours(rand(0, 23))->subMinutes(rand(0, 59));
            $orderCode = 'ORD-'.strtoupper(Str::random(8));
            $user = $users->random();
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => $orderCode,
                'order_id' => $orderCode,
                'total_amount' => 0, // calculated below
                'total' => 0,
                'subtotal' => 0,
                'delivery_charge' => 0,
                'name' => $user->name,
                'mobile' => '+91 98765 43210',
                'email' => $user->email,
                'address' => '101 Spice Garden Road',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'pincode' => '400001',
                'country' => 'India',
                'payment_method' => 'online',
                'status' => $this->weightedRandom($statuses),
                'payment_status' => $this->weightedRandom($paymentStatuses),
                'created_at' => $date,
                'updated_at' => $date,
            ]);

            $numItems = rand(1, 4);
            $totalAmount = 0;
            $orderProducts = $products->random($numItems);

            foreach ($orderProducts as $product) {
                $quantity = rand(1, 3);
                $unitPrice = $product->price;
                $totalPrice = $quantity * $unitPrice;
                $totalAmount += $totalPrice;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_slug' => $product->slug,
                    'sku' => $product->sku,
                    'unit' => $product->unit ?? 'pack',
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $totalPrice,
                    'total_price' => $totalPrice,
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);
            }

            $order->update([
                'total_amount' => $totalAmount,
                'total' => $totalAmount,
                'subtotal' => $totalAmount,
            ]);
        }
    }

    private function weightedRandom(array $weights): string
    {
        $rand = rand(1, 100);
        $current = 0;
        foreach ($weights as $key => $weight) {
            $current += $weight;
            if ($rand <= $current) {
                return $key;
            }
        }

        return array_key_first($weights);
    }
}
