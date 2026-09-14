<?php

use App\Mail\AdminOrderMail;
use App\Mail\OrderCustomerMail;
use App\Models\AdminProfile;
use App\Models\Order;
use App\Models\RefundRequest;
use App\Models\ReturnRequest;
use App\Models\User;
use App\Services\EmailNotificationService;
use Illuminate\Support\Facades\Mail;

test('email notification service dynamically resolves admin email from database', function (): void {
    $adminUser = User::factory()->create([
        'email' => 'admin_dynamic_test@example.com',
        'is_admin' => true,
    ]);

    AdminProfile::factory()->create([
        'user_id' => $adminUser->id,
        'email' => 'admin_profile_dynamic@example.com',
    ]);

    $service = new EmailNotificationService;

    expect($service->resolveAdminEmail())->toBe('admin_profile_dynamic@example.com');
});

test('admin receives email when new order is placed', function (): void {
    Mail::fake();

    $adminUser = User::factory()->create([
        'email' => 'admin_order_test@example.com',
        'is_admin' => true,
    ]);

    AdminProfile::factory()->create([
        'user_id' => $adminUser->id,
        'email' => 'admin_order_profile@example.com',
    ]);

    $order = Order::factory()->create([
        'email' => 'customer_new_order@example.com',
        'status' => 'Pending',
    ]);

    app(EmailNotificationService::class)->sendOrderPlaced($order);

    Mail::assertQueued(AdminOrderMail::class, function ($mail) {
        return $mail->hasTo('admin_order_profile@example.com') && $mail->event === 'new_order';
    });

    Mail::assertQueued(OrderCustomerMail::class, function ($mail) {
        return $mail->hasTo('customer_new_order@example.com');
    });
});

test('admin receives email when order is cancelled', function (): void {
    Mail::fake();

    $adminUser = User::factory()->create([
        'email' => 'admin_cancel_test@example.com',
        'is_admin' => true,
    ]);

    AdminProfile::factory()->create([
        'user_id' => $adminUser->id,
        'email' => 'admin_cancel_profile@example.com',
    ]);

    $order = Order::factory()->create([
        'email' => 'customer_cancel@example.com',
        'status' => 'Confirmed',
    ]);

    $order->update([
        'status' => 'Cancelled',
        'cancellation_reason' => 'Customer requested cancellation.',
    ]);

    Mail::assertQueued(AdminOrderMail::class, function ($mail) {
        return $mail->hasTo('admin_cancel_profile@example.com') && $mail->event === 'order_cancelled';
    });

    Mail::assertQueued(OrderCustomerMail::class, function ($mail) {
        return $mail->hasTo('customer_cancel@example.com');
    });
});

test('admin receives email on refund request and refund status update', function (): void {
    Mail::fake();

    $adminUser = User::factory()->create([
        'email' => 'admin_refund_test@example.com',
        'is_admin' => true,
    ]);

    AdminProfile::factory()->create([
        'user_id' => $adminUser->id,
        'email' => 'admin_refund_profile@example.com',
    ]);

    $order = Order::factory()->create([
        'status' => 'Delivered',
    ]);

    $refundRequest = RefundRequest::create([
        'order_id' => $order->id,
        'amount' => 299.00,
        'reason' => 'Damaged item',
        'status' => 'Pending',
    ]);

    Mail::assertQueued(AdminOrderMail::class, function ($mail) {
        return $mail->hasTo('admin_refund_profile@example.com') && $mail->event === 'refund_requested';
    });

    $refundRequest->update([
        'status' => 'Completed',
    ]);

    Mail::assertQueued(AdminOrderMail::class, function ($mail) {
        return $mail->hasTo('admin_refund_profile@example.com') && $mail->event === 'refund_status_updated';
    });
});

test('admin receives email on return request and return status update', function (): void {
    Mail::fake();

    $adminUser = User::factory()->create([
        'email' => 'admin_return_test@example.com',
        'is_admin' => true,
    ]);

    AdminProfile::factory()->create([
        'user_id' => $adminUser->id,
        'email' => 'admin_return_profile@example.com',
    ]);

    $order = Order::factory()->create([
        'status' => 'Delivered',
    ]);

    $returnRequest = ReturnRequest::create([
        'order_id' => $order->id,
        'reason' => 'Wrong size',
        'status' => 'Pending',
    ]);

    Mail::assertQueued(AdminOrderMail::class, function ($mail) {
        return $mail->hasTo('admin_return_profile@example.com') && $mail->event === 'return_requested';
    });

    $returnRequest->update([
        'status' => 'Approved',
    ]);

    Mail::assertQueued(AdminOrderMail::class, function ($mail) {
        return $mail->hasTo('admin_return_profile@example.com') && $mail->event === 'return_status_updated';
    });
});

test('admin receives NO email for normal order status updates while customer still receives email', function (): void {
    Mail::fake();

    $adminUser = User::factory()->create([
        'email' => 'admin_normal_test@example.com',
        'is_admin' => true,
    ]);

    AdminProfile::factory()->create([
        'user_id' => $adminUser->id,
        'email' => 'admin_normal_profile@example.com',
    ]);

    $order = Order::factory()->create([
        'email' => 'customer_normal@example.com',
        'status' => 'Confirmed',
    ]);

    // Update status to Shipped
    $order->update(['status' => 'Shipped']);

    // Update status to Out for Delivery
    $order->update(['status' => 'Out for Delivery']);

    // Update status to Delivered
    $order->update(['status' => 'Delivered']);

    // Customer should have received 3 customer status emails
    Mail::assertQueued(OrderCustomerMail::class, 3);

    // Admin should have received 0 emails for normal status updates
    Mail::assertNotQueued(AdminOrderMail::class);
});
