<?php

namespace App\Services\WhatsApp;

interface WhatsAppProviderInterface
{
    /**
     * Send a WhatsApp message to a recipient.
     *
     * @param  string  $to  Recipient phone number (e.g. +919876543210 or 9876543210)
     * @param  string  $message  Text message body
     * @param  array  $metadata  Additional metadata (template, order_id, etc.)
     * @return WhatsAppSendResult
     */
    public function send(string $to, string $message, array $metadata = []): WhatsAppSendResult;
}
