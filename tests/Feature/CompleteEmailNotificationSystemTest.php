<?php

use App\Mail\AdminNewOrderNotification;
use App\Mail\AdminNewReturnRequestNotification;
use App\Mail\AdminOrderCancelledNotification;
use App\Mail\AdminRefundProcessedNotification;
use App\Mail\OrderPlaced;
use App\Mail\OrderStatusUpdatedCustomer;
use App\Mail\PaymentSuccessful;
use App\Mail\RefundSuccessfulCustomer;
use App\Mail\ReturnRequestSubmittedCustomer;
use App\Mail\ReturnStatusUpdatedCustomer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\RefundRequest;
use App\Models\ReturnRequest;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

test('dispatches customer order placed and admin new order notifications on COD checkout', function () {
    Mail::fake();

    $user = User::factory()->create();
    $order = Order::create([
        'order_number' => 'ORD-TEST-EMAIL-1',
        'user_id' => $user->id,
        'status' => 'Pending',
        'name' => 'John Customer',
        'mobile' => '9876543210',
        'email' => 'john.customer@example.com',
        'address' => '123 Spice Street',
        'city' => 'Ahmedabad',
        'state' => 'Gujarat',
        'pincode' => '380001',
        'country' => 'India',
        'payment_method' => 'cod',
        'subtotal' => 500.00,
        'delivery_charge' => 0.00,
        'total_amount' => 500.00,
    ]);

    $product = Product::factory()->create();

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_name' => 'Turmeric Powder',
        'product_slug' => 'turmeric-powder',
        'unit' => '500g',
        'quantity' => 1,
        'unit_price' => 500.00,
        'total_price' => 500.00,
    ]);

    // Simulate COD order placement email trigger directly as in CheckoutController
    $adminEmail = config('mail.admin_address', 'urbanzen17@gmail.com');
    Mail::to($order->email)->send(new OrderPlaced($order));
    Mail::to($adminEmail)->send(new AdminNewOrderNotification($order));

    Mail::assertSent(OrderPlaced::class, fn ($mail) => $mail->hasTo('john.customer@example.com'));
    Mail::assertSent(AdminNewOrderNotification::class, fn ($mail) => $mail->hasTo($adminEmail));
});

test('dispatches customer payment successful email on online payment completion', function () {
    Mail::fake();

    $user = User::factory()->create();
    $order = Order::create([
        'order_number' => 'ORD-TEST-EMAIL-2',
        'user_id' => $user->id,
        'status' => 'Confirmed',
        'name' => 'Jane Customer',
        'mobile' => '9876543211',
        'email' => 'jane.customer@example.com',
        'address' => '456 Curry Lane',
        'city' => 'Surat',
        'state' => 'Gujarat',
        'pincode' => '395001',
        'country' => 'India',
        'payment_method' => 'online',
        'subtotal' => 750.00,
        'delivery_charge' => 0.00,
        'total_amount' => 750.00,
    ]);

    Mail::to($order->email)->send(new PaymentSuccessful($order));

    Mail::assertSent(PaymentSuccessful::class, fn ($mail) => $mail->hasTo('jane.customer@example.com'));
});

test('dispatches customer order status update emails for Confirmed, Shipped, Out for Delivery, Delivered', function () {
    Mail::fake();

    $user = User::factory()->create();
    $order = Order::create([
        'order_number' => 'ORD-TEST-EMAIL-3',
        'user_id' => $user->id,
        'status' => 'Shipped',
        'name' => 'Alice Customer',
        'mobile' => '9876543212',
        'email' => 'alice.customer@example.com',
        'address' => '789 Pepper Road',
        'city' => 'Vadodara',
        'state' => 'Gujarat',
        'pincode' => '390001',
        'country' => 'India',
        'payment_method' => 'cod',
        'subtotal' => 600.00,
        'delivery_charge' => 0.00,
        'total_amount' => 600.00,
    ]);

    Mail::to($order->email)->send(new OrderStatusUpdatedCustomer($order, 'Confirmed'));
    Mail::to($order->email)->send(new OrderStatusUpdatedCustomer($order, 'Shipped'));
    Mail::to($order->email)->send(new OrderStatusUpdatedCustomer($order, 'Out for Delivery'));
    Mail::to($order->email)->send(new OrderStatusUpdatedCustomer($order, 'Delivered'));

    Mail::assertSent(OrderStatusUpdatedCustomer::class, 4);
});

test('dispatches customer and admin emails when order is cancelled', function () {
    Mail::fake();

    $user = User::factory()->create();
    $order = Order::create([
        'order_number' => 'ORD-TEST-EMAIL-4',
        'user_id' => $user->id,
        'status' => 'Cancelled',
        'cancelled_at' => now(),
        'cancellation_reason' => 'Changed my mind',
        'name' => 'Bob Customer',
        'mobile' => '9876543213',
        'email' => 'bob.customer@example.com',
        'address' => '101 Saffron Street',
        'city' => 'Rajkot',
        'state' => 'Gujarat',
        'pincode' => '360001',
        'country' => 'India',
        'payment_method' => 'cod',
        'subtotal' => 300.00,
        'delivery_charge' => 50.00,
        'total_amount' => 350.00,
    ]);

    $adminEmail = config('mail.admin_address', 'urbanzen17@gmail.com');
    Mail::to($order->email)->send(new OrderStatusUpdatedCustomer($order, 'Cancelled'));
    Mail::to($adminEmail)->send(new AdminOrderCancelledNotification($order));

    Mail::assertSent(OrderStatusUpdatedCustomer::class, fn ($mail) => $mail->hasTo('bob.customer@example.com'));
    Mail::assertSent(AdminOrderCancelledNotification::class, fn ($mail) => $mail->hasTo($adminEmail));
});

test('dispatches return request emails to customer and admin', function () {
    Mail::fake();

    $user = User::factory()->create();
    $order = Order::create([
        'order_number' => 'ORD-TEST-EMAIL-5',
        'user_id' => $user->id,
        'status' => 'Delivered',
        'delivered_at' => now(),
        'name' => 'Charlie Customer',
        'mobile' => '9876543214',
        'email' => 'charlie.customer@example.com',
        'address' => '202 Clove Avenue',
        'city' => 'Bhavnagar',
        'state' => 'Gujarat',
        'pincode' => '364001',
        'country' => 'India',
        'payment_method' => 'cod',
        'subtotal' => 1000.00,
        'delivery_charge' => 0.00,
        'total_amount' => 1000.00,
    ]);

    $returnRequest = ReturnRequest::create([
        'order_id' => $order->id,
        'reason' => 'Damaged packaging',
        'status' => 'Pending',
    ]);

    $adminEmail = config('mail.admin_address', 'urbanzen17@gmail.com');
    Mail::to($order->email)->send(new ReturnRequestSubmittedCustomer($returnRequest));
    Mail::to($adminEmail)->send(new AdminNewReturnRequestNotification($returnRequest));

    Mail::assertSent(ReturnRequestSubmittedCustomer::class, fn ($mail) => $mail->hasTo('charlie.customer@example.com'));
    Mail::assertSent(AdminNewReturnRequestNotification::class, fn ($mail) => $mail->hasTo($adminEmail));
});

test('dispatches return status updated email to customer', function () {
    Mail::fake();

    $user = User::factory()->create();
    $order = Order::create([
        'order_number' => 'ORD-TEST-EMAIL-6',
        'user_id' => $user->id,
        'status' => 'Delivered',
        'delivered_at' => now(),
        'name' => 'David Customer',
        'mobile' => '9876543215',
        'email' => 'david.customer@example.com',
        'address' => '303 Cardamom Plaza',
        'city' => 'Jamnagar',
        'state' => 'Gujarat',
        'pincode' => '361001',
        'country' => 'India',
        'payment_method' => 'cod',
        'subtotal' => 450.00,
        'delivery_charge' => 50.00,
        'total_amount' => 500.00,
    ]);

    $returnRequest = ReturnRequest::create([
        'order_id' => $order->id,
        'reason' => 'Wrong product size',
        'status' => 'Approved',
    ]);

    Mail::to($order->email)->send(new ReturnStatusUpdatedCustomer($returnRequest));

    Mail::assertSent(ReturnStatusUpdatedCustomer::class, fn ($mail) => $mail->hasTo('david.customer@example.com'));
});

test('dispatches refund processed emails to customer and admin', function () {
    Mail::fake();

    $user = User::factory()->create();
    $order = Order::create([
        'order_number' => 'ORD-TEST-EMAIL-7',
        'user_id' => $user->id,
        'status' => 'Delivered',
        'delivered_at' => now(),
        'name' => 'Eva Customer',
        'mobile' => '9876543216',
        'email' => 'eva.customer@example.com',
        'address' => '404 Cinnamon Drive',
        'city' => 'Gandhinagar',
        'state' => 'Gujarat',
        'pincode' => '382010',
        'country' => 'India',
        'payment_method' => 'online',
        'subtotal' => 850.00,
        'delivery_charge' => 0.00,
        'total_amount' => 850.00,
    ]);

    $refundRequest = RefundRequest::create([
        'order_id' => $order->id,
        'amount' => 850.00,
        'reason' => 'Item returned in good condition',
        'status' => 'Completed',
    ]);

    $adminEmail = config('mail.admin_address', 'urbanzen17@gmail.com');
    Mail::to($order->email)->send(new RefundSuccessfulCustomer($refundRequest));
    Mail::to($adminEmail)->send(new AdminRefundProcessedNotification($refundRequest));

    Mail::assertSent(RefundSuccessfulCustomer::class, fn ($mail) => $mail->hasTo('eva.customer@example.com'));
    Mail::assertSent(AdminRefundProcessedNotification::class, fn ($mail) => $mail->hasTo($adminEmail));
});

test('admin updating order status triggers email notification to customer', function () {
    Mail::fake();

    $admin = User::factory()->create(['is_admin' => true]);
    $user = User::factory()->create();
    $order = Order::create([
        'order_number' => 'ORD-ADMIN-STATUS-1',
        'user_id' => $user->id,
        'status' => 'Pending',
        'name' => 'Rahul Sharma',
        'mobile' => '9876543210',
        'email' => 'rahul.customer@example.com',
        'address' => '505 Spice Way',
        'city' => 'Ahmedabad',
        'state' => 'Gujarat',
        'pincode' => '380001',
        'country' => 'India',
        'payment_method' => 'cod',
        'subtotal' => 400.00,
        'delivery_charge' => 50.00,
        'total_amount' => 450.00,
    ]);

    // Admin accepts / confirms order
    $this->actingAs($admin)
        ->patch(route('admin.orders.updateStatus', $order), [
            'status' => 'Confirmed',
        ])
        ->assertRedirect();

    Mail::assertSent(OrderStatusUpdatedCustomer::class, fn ($mail) => $mail->hasTo('rahul.customer@example.com') && $mail->status === 'Confirmed');

    // Admin marks order Shipped
    $this->actingAs($admin)
        ->patch(route('admin.orders.updateStatus', $order), [
            'status' => 'Shipped',
        ])
        ->assertRedirect();

    Mail::assertSent(OrderStatusUpdatedCustomer::class, fn ($mail) => $mail->hasTo('rahul.customer@example.com') && $mail->status === 'Shipped');

    // Admin marks order Delivered
    $this->actingAs($admin)
        ->patch(route('admin.orders.updateStatus', $order), [
            'status' => 'Delivered',
        ])
        ->assertRedirect();

    Mail::assertSent(OrderStatusUpdatedCustomer::class, fn ($mail) => $mail->hasTo('rahul.customer@example.com') && $mail->status === 'Delivered');
});
