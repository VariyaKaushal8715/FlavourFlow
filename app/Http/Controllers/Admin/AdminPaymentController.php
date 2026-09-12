<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AdminPaymentController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('access-admin');

        $query = Payment::with(['order', 'user']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('cashfree_order_id', 'like', "%{$search}%")
                    ->orWhere('cashfree_payment_id', 'like', "%{$search}%")
                    ->orWhere('razorpay_order_id', 'like', "%{$search}%")
                    ->orWhere('razorpay_payment_id', 'like', "%{$search}%")
                    ->orWhereHas('order', function ($oq) use ($search) {
                        $oq->where('order_number', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $payments = $query->latest()->paginate(15)->withQueryString();

        return view('admin.payments.index', [
            'payments' => $payments,
            'filters' => $request->only(['search', 'status']),
        ]);
    }
}
