<?php

namespace App\Support;

use App\Models\Order;
use App\Services\InvoiceService;

class PdfReceiptGenerator
{
    /**
     * Generate PDF binary string for an order using the InvoiceService.
     */
    public function generate(Order $order): string
    {
        /** @var InvoiceService $service */
        $service = app(InvoiceService::class);

        return $service->generatePdf($order);
    }
}
