<?php

namespace App\Services;

use App\Models\BusinessSetting;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    /**
     * Retrieve existing invoice snapshot or atomically allocate a new sequential invoice number and snapshot.
     *
     * @return array<string, mixed>
     */
    public function getOrCreateSnapshot(Order $order): array
    {
        // 1. Return existing snapshot if already issued (historical stability)
        if (! empty($order->invoice_number) && ! empty($order->invoice_snapshot)) {
            return $order->invoice_snapshot;
        }

        // 2. Atomically allocate invoice number and persist snapshot under DB transaction
        return DB::transaction(function () use ($order) {
            /** @var Order $lockedOrder */
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->first();

            if (! empty($lockedOrder->invoice_number) && ! empty($lockedOrder->invoice_snapshot)) {
                return $lockedOrder->invoice_snapshot;
            }

            $setting = BusinessSetting::currentForUpdate();
            $prefix = strtoupper(trim($setting->invoice_prefix ?: 'INV'));
            $year = now()->format('Y');
            $nextNum = max(1, (int) ($setting->next_invoice_number ?: 1001));

            $invoiceNumber = sprintf('%s-%s-%04d', $prefix, $year, $nextNum);

            // Guard against numbering collisions
            while (Order::where('invoice_number', $invoiceNumber)->exists()) {
                $nextNum++;
                $invoiceNumber = sprintf('%s-%s-%04d', $prefix, $year, $nextNum);
            }

            $setting->update(['next_invoice_number' => $nextNum + 1]);

            $snapshot = $this->buildSnapshot($lockedOrder, $setting, $invoiceNumber);

            $lockedOrder->update([
                'invoice_number' => $invoiceNumber,
                'invoiced_at' => now(),
                'invoice_snapshot' => $snapshot,
            ]);

            return $snapshot;
        });
    }

    /**
     * Build an immutable snapshot of business, customer, order, and tax data.
     *
     * @return array<string, mixed>
     */
    protected function buildSnapshot(Order $order, BusinessSetting $setting, string $invoiceNumber): array
    {
        $order->loadMissing('items');

        $documentType = $this->determineDocumentType($order);
        $paymentStatusLabel = $this->determinePaymentStatusLabel($order);

        $latestPayment = $order->payments()->latest()->first();
        $txnRef = $latestPayment?->customer_utr
            ?: $latestPayment?->transaction_id
            ?: $latestPayment?->razorpay_payment_id
            ?: $latestPayment?->cashfree_payment_id
            ?: 'N/A';

        return [
            'invoice' => [
                'number' => $invoiceNumber,
                'date' => now()->format('d M Y, h:i A'),
                'document_type' => $documentType,
                'payment_status_label' => $paymentStatusLabel,
                'is_paid' => in_array($order->payment_status, ['paid', 'captured'], true) || ($order->status === 'Confirmed' && $order->payment_method !== 'cod'),
                'transaction_reference' => $txnRef,
            ],
            'business' => [
                'name' => $setting->store_name ?: 'FlavourFlow',
                'legal_name' => $setting->legal_name,
                'logo_url' => $setting->logoUrl(),
                'address' => $setting->business_address ?: 'Patan, Gujarat, India',
                'billing_address' => $setting->billing_address ?: $setting->business_address,
                'phone' => $setting->phone ?: '+91 99999 99999',
                'email' => $setting->email ?: 'support@flavourflow.com',
                'website' => $setting->website_url ?: config('app.url', 'https://flavourflow.test'),
                'gstin' => $setting->gstin,
                'tax_rate' => (float) $setting->tax_rate,
                'tax_label' => $setting->tax_label ?: 'GST',
                'tax_mode' => $setting->tax_mode ?: 'inclusive',
                'footer_note' => $setting->invoice_footer ?: 'Thank you for choosing FlavourFlow! Authentic spices, crafted fresh.',
                'payment_instructions' => $setting->payment_instructions,
                'return_policy' => $setting->return_policy_text,
            ],
            'customer' => [
                'name' => $order->name,
                'email' => $order->email,
                'mobile' => $order->mobile,
                'address' => $order->address,
                'city' => $order->city,
                'state' => $order->state,
                'pincode' => $order->pincode,
                'country' => $order->country,
                'full_address' => trim($order->address.', '.$order->city.', '.$order->state.' - '.$order->pincode.', '.$order->country, ', -'),
            ],
            'order' => [
                'number' => $order->order_number,
                'date' => $order->created_at ? $order->created_at->format('d M Y, h:i A') : now()->format('d M Y, h:i A'),
                'status' => $order->status,
                'payment_method' => $order->payment_method,
                'payment_status' => $order->payment_status ?: 'pending',
                'subtotal' => (float) $order->subtotal,
                'delivery_charge' => (float) $order->delivery_charge,
                'discount_amount' => (float) ($order->discount_amount ?: 0.00),
                'coupon_code' => $order->coupon_code,
                'total_amount' => (float) $order->total_amount,
            ],
            'items' => $order->items->map(function ($item) {
                return [
                    'product_name' => $item->product_name,
                    'sku' => $item->sku ?: 'N/A',
                    'unit' => $item->unit ?: '100g',
                    'quantity' => (int) $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'total_price' => (float) $item->total_price,
                ];
            })->values()->all(),
        ];
    }

    /**
     * Determine legal document title based on settlement state.
     */
    public function determineDocumentType(Order $order): string
    {
        $isSettled = in_array($order->payment_status, ['paid', 'captured'], true)
            || ($order->status === 'Confirmed' && $order->payment_method !== 'cod');

        if ($isSettled) {
            return 'TAX INVOICE';
        }

        if ($order->payment_method === 'cod') {
            return 'PROFORMA INVOICE (CASH ON DELIVERY)';
        }

        return 'PROFORMA INVOICE / ORDER SUMMARY';
    }

    /**
     * Determine human-readable payment status badge.
     */
    public function determinePaymentStatusLabel(Order $order): string
    {
        if (in_array(strtolower($order->status), ['cancelled', 'refunded'], true)) {
            return strtoupper($order->status);
        }

        if (in_array($order->payment_status, ['paid', 'captured'], true) || ($order->status === 'Confirmed' && $order->payment_method !== 'cod')) {
            return 'PAID & VERIFIED';
        }

        if ($order->payment_method === 'upi') {
            return 'PAYMENT PENDING (DIRECT UPI — MANUAL CONFIRMATION REQUIRED)';
        }

        if ($order->payment_method === 'cod') {
            return 'PAYMENT DUE ON DELIVERY';
        }

        return 'PAYMENT PENDING';
    }

    /**
     * Generate raw PDF binary bytes for an order using its snapshot data.
     */
    public function generatePdf(Order $order): string
    {
        $data = $this->getOrCreateSnapshot($order);

        $business = $data['business'] ?? [];
        $invoice = $data['invoice'] ?? [];
        $customer = $data['customer'] ?? [];
        $orderData = $data['order'] ?? [];
        $items = $data['items'] ?? [];

        // PDF Objects
        $objects = [];
        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[2] = '<< /Type /Pages /Kids [ 3 0 R ] /Count 1 >>';
        $objects[3] = '<< /Type /Page /Parent 2 0 R /Resources 4 0 R /MediaBox [ 0 0 595 842 ] /Contents 5 0 R >>';
        $objects[4] = '<< /Font << /F1 << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> /F2 << /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >> /F3 << /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Oblique >> >> >>';

        $stream = '';

        // Helper closures for drawing
        $esc = fn (?string $text) => str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], iconv('UTF-8', 'Windows-1252//IGNORE', (string) ($text ?? '')));

        // 1. Top Decorative Brand Bar
        $stream .= "0.706 0.137 0.094 rg\n"; // Brand Red: #b42318
        $stream .= "0 832 595 10 re\n";
        $stream .= "f\n";

        // 2. Business Header (Left)
        $stream .= "BT\n";
        $stream .= "/F2 18 Tf\n";
        $stream .= "0.039 0.039 0.043 rg\n"; // Ink: #09090b
        $stream .= "45 795 Td\n";
        $stream .= '('.$esc($business['name']).") Tj\n";
        $stream .= "ET\n";

        $y = 780;
        if (! empty($business['legal_name']) && $business['legal_name'] !== $business['name']) {
            $stream .= "BT\n/F3 8.5 Tf\n0.3 0.3 0.3 rg\n45 ".$y." Td\n";
            $stream .= '('.$esc($business['legal_name']).") Tj\nET\n";
            $y -= 12;
        }

        if (! empty($business['gstin'])) {
            $stream .= "BT\n/F2 8.5 Tf\n0.706 0.137 0.094 rg\n45 ".$y." Td\n";
            $stream .= '(GSTIN: '.$esc($business['gstin']).") Tj\nET\n";
            $y -= 12;
        }

        $stream .= "BT\n/F1 8 Tf\n0.35 0.35 0.35 rg\n11 TL\n45 ".$y." Td\n";
        $stream .= '('.$esc(substr($business['address'], 0, 70)).") Tj T*\n";
        $stream .= '(Phone: '.$esc($business['phone']).'  |  Email: '.$esc($business['email']).") Tj T*\n";
        if (! empty($business['website'])) {
            $stream .= '(Web: '.$esc($business['website']).") Tj T*\n";
        }
        $stream .= "ET\n";

        // 3. Document Details (Right)
        $stream .= "BT\n/F2 14 Tf\n0.706 0.137 0.094 rg\n340 795 Td\n";
        $stream .= '('.$esc($invoice['document_type']).") Tj\nET\n";

        $stream .= "BT\n/F1 8.5 Tf\n0.2 0.2 0.2 rg\n13 TL\n340 775 Td\n";
        $stream .= '(Invoice No: '.$esc($invoice['number']).") Tj T*\n";
        $stream .= '(Invoice Date: '.$esc($invoice['date']).") Tj T*\n";
        $stream .= '(Order Ref: #'.$esc($orderData['number']).") Tj T*\n";
        $stream .= '(Order Date: '.$esc($orderData['date']).") Tj T*\n";
        $stream .= "ET\n";

        // 4. Status Badge Banner
        $isPaid = (bool) ($invoice['is_paid'] ?? false);
        if ($isPaid) {
            $stream .= "0.92 0.98 0.94 rg\n"; // Light green
            $stream .= "0.13 0.55 0.27 RG\n"; // Border green
            $statusText = 'STATUS: PAID & VERIFIED';
            $statusColor = '0.08 0.40 0.18 rg';
        } else {
            $stream .= "1.0 0.97 0.90 rg\n"; // Light amber
            $stream .= "0.85 0.55 0.10 RG\n"; // Border amber
            $statusText = 'STATUS: '.($invoice['payment_status_label'] ?? 'PAYMENT PENDING');
            $statusColor = '0.60 0.30 0.05 rg';
        }
        $stream .= "0.75 w\n";
        $stream .= "45 690 505 22 re\n";
        $stream .= "B\n";

        $stream .= "BT\n/F2 8.5 Tf\n".$statusColor."\n55 697 Td\n";
        $stream .= '('.$esc($statusText).") Tj\nET\n";

        if (! empty($invoice['transaction_reference']) && $invoice['transaction_reference'] !== 'N/A') {
            $stream .= "BT\n/F1 8 Tf\n0.3 0.3 0.3 rg\n340 697 Td\n";
            $stream .= '(Ref/UTR: '.$esc($invoice['transaction_reference']).") Tj\nET\n";
        }

        // 5. Customer Details Box
        $stream .= "0.98 0.98 0.98 rg\n";
        $stream .= "0.85 0.85 0.85 RG\n";
        $stream .= "0.5 w\n";
        $stream .= "45 615 505 65 re\n";
        $stream .= "B\n";

        $stream .= "BT\n/F2 9.5 Tf\n0.1 0.1 0.1 rg\n55 665 Td\n(BILLED & SHIPPED TO:) Tj\nET\n";
        $stream .= "BT\n/F1 8.5 Tf\n0.2 0.2 0.2 rg\n12 TL\n55 650 Td\n";
        $stream .= '('.$esc($customer['name']).'  |  '.$esc($customer['mobile']).'  |  '.$esc($customer['email']).") Tj T*\n";
        $stream .= '('.$esc(substr($customer['full_address'], 0, 95)).") Tj T*\n";
        if (strlen($customer['full_address']) > 95) {
            $stream .= '('.$esc(substr($customer['full_address'], 95, 95)).") Tj T*\n";
        }
        $stream .= "ET\n";

        // 6. Items Table Header
        $stream .= "0.15 0.15 0.15 rg\n"; // Dark header
        $stream .= "45 590 505 20 re\n";
        $stream .= "f\n";

        $stream .= "BT\n/F2 8.5 Tf\n1 1 1 rg\n";
        $stream .= "55 596 Td\n(#) Tj\n";
        $stream .= "75 596 Td\n(ITEM DESCRIPTION) Tj\n";
        $stream .= "290 596 Td\n(SKU) Tj\n";
        $stream .= "360 596 Td\n(QTY) Tj\n";
        $stream .= "415 596 Td\n(UNIT PRICE) Tj\n";
        $stream .= "485 596 Td\n(TOTAL) Tj\n";
        $stream .= "ET\n";

        // Table Rows
        $rowY = 572;
        $index = 1;
        foreach ($items as $item) {
            // Zebra striping
            if ($index % 2 === 0) {
                $stream .= "0.97 0.97 0.97 rg\n";
                $stream .= '45 '.($rowY - 4)." 505 16 re\n";
                $stream .= "f\n";
            }

            $stream .= "BT\n/F1 8 Tf\n0.1 0.1 0.1 rg\n";
            $stream .= '55 '.$rowY.' Td\n('.$index.") Tj\n";
            $stream .= '75 '.$rowY.' Td\n('.$esc(substr($item['product_name'], 0, 36)).") Tj\n";
            $stream .= '290 '.$rowY.' Td\n('.$esc($item['sku']).") Tj\n";
            $stream .= '360 '.$rowY.' Td\n('.$item['quantity'].' x '.$esc($item['unit']).") Tj\n";
            $stream .= '415 '.$rowY.' Td\n(Rs. '.number_format($item['unit_price'], 2).") Tj\n";
            $stream .= '485 '.$rowY.' Td\n(Rs. '.number_format($item['total_price'], 2).") Tj\n";
            $stream .= "ET\n";

            $rowY -= 16;
            $index++;
        }

        // Horizontal line under table
        $stream .= "0.7 w\n0.8 0.8 0.8 RG\n";
        $stream .= '45 '.$rowY.' m 550 '.$rowY." l S\n";

        // 7. Summary Totals Box (Right) & Notes (Left)
        $summaryY = $rowY - 14;

        // Notes (Left)
        $stream .= "BT\n/F2 8 Tf\n0.2 0.2 0.2 rg\n55 ".$summaryY." Td\n(PAYMENT METHOD:) Tj\nET\n";
        $stream .= "BT\n/F1 8 Tf\n0.3 0.3 0.3 rg\n145 ".$summaryY." Td\n(".strtoupper($orderData['payment_method']).") Tj\nET\n";

        if (! empty($business['payment_instructions'])) {
            $stream .= "BT\n/F3 7 Tf\n0.4 0.4 0.4 rg\n55 ".($summaryY - 12)." Td\n";
            $stream .= '('.$esc(substr($business['payment_instructions'], 0, 55)).") Tj\nET\n";
        }

        // Totals Rows (Right)
        $totLabelX = 360;
        $totValX = 475;
        $curY = $summaryY;

        // Subtotal
        $stream .= "BT\n/F1 8.5 Tf\n0.3 0.3 0.3 rg\n".$totLabelX.' '.$curY." Td\n(Items Subtotal:) Tj\nET\n";
        $stream .= "BT\n/F1 8.5 Tf\n0.1 0.1 0.1 rg\n".$totValX.' '.$curY.' Td\n(Rs. '.number_format($orderData['subtotal'], 2).") Tj\nET\n";
        $curY -= 13;

        // Discount
        if ($orderData['discount_amount'] > 0) {
            $discLabel = 'Coupon Discount';
            if (! empty($orderData['coupon_code'])) {
                $discLabel .= ' ('.$orderData['coupon_code'].')';
            }
            $stream .= "BT\n/F1 8.5 Tf\n0.706 0.137 0.094 rg\n".$totLabelX.' '.$curY.' Td\n('.$esc($discLabel).":) Tj\nET\n";
            $stream .= "BT\n/F1 8.5 Tf\n0.706 0.137 0.094 rg\n".$totValX.' '.$curY.' Td\n(-Rs. '.number_format($orderData['discount_amount'], 2).") Tj\nET\n";
            $curY -= 13;
        }

        // Delivery Charge
        $stream .= "BT\n/F1 8.5 Tf\n0.3 0.3 0.3 rg\n".$totLabelX.' '.$curY." Td\n(Delivery Charge:) Tj\nET\n";
        $delivText = $orderData['delivery_charge'] > 0 ? 'Rs. '.number_format($orderData['delivery_charge'], 2) : 'FREE';
        $stream .= "BT\n/F1 8.5 Tf\n0.1 0.1 0.1 rg\n".$totValX.' '.$curY.' Td\n('.$delivText.") Tj\nET\n";
        $curY -= 13;

        // Taxes notice
        $taxNotice = 'Inclusive of applicable GST';
        if (! empty($business['tax_rate']) && (float) $business['tax_rate'] > 0) {
            $taxNotice .= ' ('.number_format($business['tax_rate'], 1).'%)';
        }
        $stream .= "BT\n/F3 7.5 Tf\n0.4 0.4 0.4 rg\n".$totLabelX.' '.$curY.' Td\n('.$esc($taxNotice).") Tj\nET\n";
        $stream .= "BT\n/F3 7.5 Tf\n0.4 0.4 0.4 rg\n".$totValX.' '.$curY." Td\n(Incl.) Tj\nET\n";
        $curY -= 10;

        // Divider
        $stream .= "0.8 w\n0.706 0.137 0.094 RG\n";
        $stream .= $totLabelX.' '.$curY.' m 550 '.$curY." l S\n";
        $curY -= 14;

        // Grand Total Box
        $stream .= "0.706 0.137 0.094 rg\n";
        $stream .= ($totLabelX - 5).' '.($curY - 4)." 195 20 re\n";
        $stream .= "f\n";

        $stream .= "BT\n/F2 10 Tf\n1 1 1 rg\n".$totLabelX.' '.$curY." Td\n(FINAL TOTAL:) Tj\nET\n";
        $stream .= "BT\n/F2 10 Tf\n1 1 1 rg\n".$totValX.' '.$curY.' Td\n(Rs. '.number_format($orderData['total_amount'], 2).") Tj\nET\n";

        // 8. Footer Section (Bottom)
        $footY = 110;
        $stream .= "0.5 w\n0.85 0.85 0.85 RG\n";
        $stream .= '45 '.$footY.' m 550 '.$footY." l S\n";

        $stream .= "BT\n/F2 7.5 Tf\n0.2 0.2 0.2 rg\n45 ".($footY - 14)." Td\n(RETURN & REFUND POLICY:) Tj\nET\n";
        $stream .= "BT\n/F1 7 Tf\n0.4 0.4 0.4 rg\n45 ".($footY - 24)." Td\n";
        $stream .= '('.$esc(substr($business['return_policy'] ?? 'Returns accepted within 7 days in original packaging.', 0, 110)).") Tj\nET\n";

        if (! empty($business['footer_note'])) {
            $stream .= "BT\n/F3 7 Tf\n0.4 0.4 0.4 rg\n45 ".($footY - 36)." Td\n";
            $stream .= '('.$esc(substr($business['footer_note'], 0, 110)).") Tj\nET\n";
        }

        $stream .= "BT\n/F1 6.5 Tf\n0.6 0.6 0.6 rg\n45 50 Td\n";
        $stream .= "(This is an official computer-generated document issued by FlavourFlow. No physical signature is required.) Tj\nET\n";

        // Assembly
        $objects[5] = '<< /Length '.strlen($stream)." >>\nstream\n".$stream.'endstream';

        $body = "%PDF-1.4\n";
        $offsets = [];

        for ($i = 1; $i <= 5; $i++) {
            $offsets[$i] = strlen($body);
            $body .= $i." 0 obj\n".$objects[$i]."\nendobj\n";
        }

        $startXref = strlen($body);
        $body .= "xref\n";
        $body .= "0 6\n";
        $body .= "0000000000 65535 f \n";
        for ($i = 1; $i <= 5; $i++) {
            $body .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }

        $body .= "trailer\n";
        $body .= "<< /Size 6 /Root 1 0 R >>\n";
        $body .= "startxref\n";
        $body .= $startXref."\n";
        $body .= "%%EOF\n";

        return $body;
    }
}
