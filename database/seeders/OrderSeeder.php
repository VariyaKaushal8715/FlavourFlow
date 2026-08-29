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
            $order = Order::create([
                'user_id' => $users->random()->id,
                'order_number' => 'ORD-'.strtoupper(Str::random(8)),
                'total_amount' => 0, // calculated below
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
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);
            }

            $order->update(['total_amount' => $totalAmount]);
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
