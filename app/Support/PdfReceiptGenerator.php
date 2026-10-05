<?php

namespace App\Support;

class PdfReceiptGenerator
{
    /**
     * Generate a raw PDF binary string for an order.
     */
    public function generate($order): string
    {
        // Generate minimal PDF document structure
        $objects = [];

        // 1: Catalog
        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        // 2: Pages list
        $objects[2] = '<< /Type /Pages /Kids [ 3 0 R ] /Count 1 >>';
        // 3: Page content details (using Helvetica / Helvetica-Bold standard PDF Type1 fonts)
        $objects[3] = '<< /Type /Page /Parent 2 0 R /Resources 4 0 R /MediaBox [ 0 0 595 842 ] /Contents 5 0 R >>';
        // 4: Fonts catalog
        $objects[4] = '<< /Font << /F1 << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> /F2 << /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >> >> >>';

        // 5: Contents stream drawing invoice details
        $stream = '';

        // Draw Header
        $stream .= "BT\n";
        $stream .= "/F2 20 Tf\n";
        $stream .= "40 TL\n";
        $stream .= "50 780 Td\n";
        $stream .= "(FLAVOURFLOW INVOICE) Tj T*\n";
        $stream .= "ET\n";

        $stream .= "BT\n";
        $stream .= "/F1 10 Tf\n";
        $stream .= "15 TL\n";
        $stream .= "50 740 Td\n";
        $stream .= '(Order ID: '.$order->order_number.") Tj T*\n";
        $stream .= '(Date: '.$order->created_at->format('Y-m-d H:i')." IST) Tj T*\n";
        $stream .= '(Status: '.$order->status.") Tj T*\n";
        $stream .= '(Payment Method: '.strtoupper($order->payment_method).") Tj T*\n";
        $stream .= "ET\n";

        $stream .= "BT\n";
        $stream .= "/F2 12 Tf\n";
        $stream .= "50 670 Td\n";
        $stream .= "(Customer Details:) Tj T*\n";
        $stream .= "ET\n";

        $stream .= "BT\n";
        $stream .= "/F1 10 Tf\n";
        $stream .= "15 TL\n";
        $stream .= "50 650 Td\n";
        $stream .= '(Name: '.$order->name.") Tj T*\n";
        $stream .= '(Email: '.$order->email.") Tj T*\n";
        $stream .= '(Mobile: '.$order->mobile.") Tj T*\n";

        // Escape address slashes or braces
        $fullAddress = $order->address.', '.$order->city.', '.$order->state.' - '.$order->pincode.', '.$order->country;
        $escapedAddress = str_replace(['(', ')'], ['\\(', '\\)'], $fullAddress);

        $stream .= '(Address: '.substr($escapedAddress, 0, 75).") Tj T*\n";
        if (strlen($escapedAddress) > 75) {
            $stream .= '('.substr($escapedAddress, 75, 75).") Tj T*\n";
        }
        $stream .= "ET\n";

        // Draw horizontal divider line
        $stream .= "1.0 w\n";
        $stream .= "50 540 m\n";
        $stream .= "545 540 l\n";
        $stream .= "S\n";

        // Items Header
        $stream .= "BT\n";
        $stream .= "/F2 10 Tf\n";
        $stream .= "50 520 Td\n";
        $stream .= "(Product) Tj\n";
        $stream .= "200 0 Td\n";
        $stream .= "(Qty) Tj\n";
        $stream .= "100 0 Td\n";
        $stream .= "(Price) Tj\n";
        $stream .= "100 0 Td\n";
        $stream .= "(Total) Tj\n";
        $stream .= "ET\n";

        $y = 500;
        foreach ($order->items as $item) {
            $prodName = str_replace(['(', ')'], ['\\(', '\\)'], $item->product_name);
            $stream .= "BT\n";
            $stream .= "/F1 10 Tf\n";
            $stream .= '50 '.$y." Td\n";
            $stream .= '('.substr($prodName, 0, 30).") Tj\n";
            $stream .= "200 0 Td\n";
            $stream .= '('.$item->quantity.'x '.$item->unit.") Tj\n";
            $stream .= "100 0 Td\n";
            $stream .= '(Rs. '.number_format($item->unit_price, 2).") Tj\n";
            $stream .= "100 0 Td\n";
            $stream .= '(Rs. '.number_format($item->total_price, 2).") Tj\n";
            $stream .= "ET\n";
            $y -= 20;
        }

        // Draw Line
        $stream .= "1.0 w\n";
        $stream .= '50 '.($y - 5)." m\n";
        $stream .= '545 '.($y - 5)." l\n";
        $stream .= "S\n";

        $y -= 25;
        $discountLabel = 'Discount';
        if ($order->coupon_code) {
            $discountLabel .= ' ('.$order->coupon_code.')';
        }
        $discountVal = (float) ($order->discount_amount ?? 0.00);
        $discountStr = $discountVal > 0 ? '-Rs. '.number_format($discountVal, 2) : 'Rs. 0.00';

        // Subtotal row
        $stream .= "BT\n/F1 10 Tf\n350 ".$y." Td\n(Subtotal:) Tj\nET\n";
        $stream .= "BT\n/F1 10 Tf\n480 ".$y." Td\n(Rs. ".number_format($order->subtotal, 2).") Tj\nET\n";

        $y -= 15;
        // Discount row
        $stream .= "BT\n/F1 10 Tf\n350 ".$y." Td\n(".$discountLabel.":) Tj\nET\n";
        $stream .= "BT\n/F1 10 Tf\n480 ".$y." Td\n(".$discountStr.") Tj\nET\n";

        $y -= 15;
        // Delivery Charge row
        $stream .= "BT\n/F1 10 Tf\n350 ".$y." Td\n(Delivery Charge:) Tj\nET\n";
        $stream .= "BT\n/F1 10 Tf\n480 ".$y." Td\n(Rs. ".number_format($order->delivery_charge, 2).") Tj\nET\n";

        // Draw Line below summary
        $y -= 8;
        $stream .= "1.0 w\n";
        $stream .= '350 '.$y." m\n";
        $stream .= '545 '.$y." l\n";
        $stream .= "S\n";

        $y -= 18;
        // Grand Total row
        $stream .= "BT\n/F2 12 Tf\n350 ".$y." Td\n(Grand Total:) Tj\nET\n";
        $stream .= "BT\n/F2 12 Tf\n480 ".$y." Td\n(Rs. ".number_format($order->total_amount, 2).") Tj\nET\n";

        $objects[5] = '<< /Length '.strlen($stream)." >>\nstream\n".$stream.'endstream';

        // Build PDF document bytes
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
