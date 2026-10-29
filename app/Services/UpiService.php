<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentGatewaySetting;
use InvalidArgumentException;

class UpiService
{
    public function __construct(
        protected QrCodeService $qrCodeService
    ) {}

    /**
     * Validate UPI Virtual Payment Address (VPA) format.
     * Standard UPI format: username@bankname (e.g. merchant@okhdfcbank, store.1@upi)
     */
    public function isValidUpiId(?string $upiId): bool
    {
        if (empty($upiId)) {
            return false;
        }

        $trimmed = trim($upiId);

        // VPA format: 2-256 alphanumeric characters, dots, hyphens or underscores, followed by @ and 2-64 alpha bank code
        return (bool) preg_match('/^[a-zA-Z0-9.\-_]{2,256}@[a-zA-Z]{2,64}$/', $trimmed);
    }

    /**
     * Build a scannable standard UPI Payment URI (`upi://pay`).
     *
     * @param  string  $upiId  Merchant UPI VPA (e.g. merchant@upi)
     * @param  string  $payeeName  Payee Business / Display Name (e.g. FlavourFlow Spices)
     * @param  float  $amount  Server-calculated amount in INR
     * @param  string  $orderReference  Unique Order Number (e.g. ORD-20261029-XXXXXX)
     * @param  string|null  $note  Transaction note
     *
     * @throws InvalidArgumentException
     */
    public function buildPaymentUri(
        string $upiId,
        string $payeeName,
        float $amount,
        string $orderReference,
        ?string $note = null
    ): string {
        $trimmedUpiId = trim($upiId);
        if (! $this->isValidUpiId($trimmedUpiId)) {
            throw new InvalidArgumentException("Invalid merchant UPI ID format: '{$upiId}'. Must match username@bank.");
        }

        if ($amount <= 0) {
            throw new InvalidArgumentException("Payment amount must be greater than zero, got: '{$amount}'.");
        }

        $trimmedRef = trim($orderReference);
        if (empty($trimmedRef)) {
            throw new InvalidArgumentException('Order reference cannot be empty.');
        }

        $trimmedName = trim($payeeName);
        if (empty($trimmedName)) {
            $trimmedName = config('app.name', 'FlavourFlow Spices');
        }

        $formattedAmount = number_format($amount, 2, '.', '');
        $rawNote = ! empty($note) ? trim($note) : "Order {$trimmedRef}";
        // Clean note for maximum compatibility with all UPI apps (NPCI recommends alphanumeric & spaces only)
        $cleanNote = preg_replace('/[#]/', '', $rawNote);
        $finalNote = trim(substr($cleanNote, 0, 40));

        // Safely RFC 3986 encode URI parameters
        // 'pa' maintains standard VPA with literal @ for maximum UPI app interoperability
        $paParam = rawurlencode($trimmedUpiId);
        // Replace %40 with literal @ in pa parameter if encoded, as standard UPI apps require @ in VPA
        $paParam = str_replace('%40', '@', $paParam);

        $params = [
            'pa='.$paParam,
            'pn='.rawurlencode($trimmedName),
            'am='.rawurlencode($formattedAmount),
            'cu=INR',
            'tn='.rawurlencode($finalNote),
            'tr='.rawurlencode($trimmedRef),
        ];

        return 'upi://pay?'.implode('&', $params);
    }

    /**
     * Generate scannable QR Code as SVG vector markup.
     */
    public function generateQrSvg(string $paymentUri, int $size = 300, int $margin = 4): string
    {
        return $this->qrCodeService->renderSvg(
            text: $paymentUri,
            size: $size,
            margin: $margin,
            fgColor: '#000000',
            bgColor: '#ffffff',
            ecLevel: QrCodeService::EC_LEVEL_M
        );
    }

    /**
     * Generate scannable QR Code as Base64 Data URI.
     */
    public function generateQrDataUri(string $paymentUri, int $size = 300, int $margin = 4): string
    {
        return $this->qrCodeService->renderDataUri(
            text: $paymentUri,
            size: $size,
            margin: $margin,
            fgColor: '#000000',
            bgColor: '#ffffff',
            ecLevel: QrCodeService::EC_LEVEL_M
        );
    }

    /**
     * Get app-specific launch URIs for popular UPI apps.
     *
     * @return array<string, array{name: string, package: string, uri: string, fallback_uri: string, hint: string}>
     */
    public function getUpiAppLaunchers(string $paymentUri): array
    {
        $queryString = parse_url($paymentUri, PHP_URL_QUERY) ?? '';

        return [
            'generic' => [
                'name' => 'All UPI Apps',
                'package' => 'generic',
                'uri' => 'upi://pay?'.$queryString,
                'fallback_uri' => 'upi://pay?'.$queryString,
                'hint' => 'Opens native Android app chooser for all installed UPI apps',
            ],
            'gpay' => [
                'name' => 'Google Pay',
                'package' => 'com.google.android.apps.nbu.paisa.user',
                'uri' => 'tez://upi/pay?'.$queryString,
                'fallback_uri' => 'upi://pay?'.$queryString,
                'hint' => 'Launch Google Pay directly',
            ],
            'phonepe' => [
                'name' => 'PhonePe',
                'package' => 'com.phonepe.app',
                'uri' => 'phonepe://pay?'.$queryString,
                'fallback_uri' => 'upi://pay?'.$queryString,
                'hint' => 'Launch PhonePe directly',
            ],
            'paytm' => [
                'name' => 'Paytm',
                'package' => 'net.one97.paytm',
                'uri' => 'paytmmp://pay?'.$queryString,
                'fallback_uri' => 'upi://pay?'.$queryString,
                'hint' => 'Launch Paytm directly',
            ],
            'bhim' => [
                'name' => 'BHIM UPI',
                'package' => 'in.org.npci.upiapp',
                'uri' => 'bhim://pay?'.$queryString,
                'fallback_uri' => 'upi://pay?'.$queryString,
                'hint' => 'Launch BHIM directly',
            ],
            'cred' => [
                'name' => 'CRED UPI',
                'package' => 'com.dreamplug.androidapp',
                'uri' => 'cred://upi/pay?'.$queryString,
                'fallback_uri' => 'upi://pay?'.$queryString,
                'hint' => 'Launch CRED directly',
            ],
        ];
    }

    /**
     * Build payment details, URI, and QR codes dynamically for an order using authoritative server values.
     *
     * @return array{
     *     upi_id: string,
     *     payee_name: string,
     *     amount: float,
     *     formatted_amount: string,
     *     order_reference: string,
     *     transaction_note: string,
     *     payment_uri: string,
     *     qr_svg: string,
     *     qr_data_uri: string,
     *     app_launchers: array<string, array{name: string, package: string, uri: string, fallback_uri: string, hint: string}>
     * }
     */
    public function getPaymentDetailsForOrder(Order $order): array
    {
        $setting = PaymentGatewaySetting::current();

        $upiId = $setting->getEffectiveUpiId();
        $payeeName = $setting->getEffectiveUpiName();
        $notePrefix = $setting->getEffectiveUpiNote();

        $orderReference = $order->order_number;
        // Authoritative server-calculated order amount
        $amount = (float) $order->total_amount;
        $note = "{$notePrefix} #{$orderReference}";

        $paymentUri = $this->buildPaymentUri($upiId, $payeeName, $amount, $orderReference, $note);
        $qrSvg = $this->generateQrSvg($paymentUri, size: 280, margin: 4);
        $qrDataUri = $this->generateQrDataUri($paymentUri, size: 280, margin: 4);

        return [
            'upi_id' => $upiId,
            'payee_name' => $payeeName,
            'amount' => $amount,
            'formatted_amount' => '₹'.number_format($amount, 2),
            'order_reference' => $orderReference,
            'transaction_note' => $note,
            'payment_uri' => $paymentUri,
            'qr_svg' => $qrSvg,
            'qr_data_uri' => $qrDataUri,
            'app_launchers' => $this->getUpiAppLaunchers($paymentUri),
        ];
    }
}
