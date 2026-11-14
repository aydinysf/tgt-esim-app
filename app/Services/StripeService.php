<?php

namespace App\Services;

use App\Models\IwcOrder;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;

class StripeService
{
    protected string $secretKey;
    protected string $publishableKey;

    public function __construct()
    {
        $this->secretKey = (string) (\App\Models\Setting::get('stripe_secret') ?: config('services.stripe.secret', ''));
        $this->publishableKey = (string) (\App\Models\Setting::get('stripe_key') ?: config('services.stripe.key', ''));
    }

    public function isConfigured(): bool
    {
        return !empty($this->secretKey);
    }

    public function getPublishableKey(): string
    {
        return $this->publishableKey;
    }

    /**
     * Create a Stripe Checkout Session for an IwcOrder
     */
    public function createCheckoutSession(IwcOrder $order, string $successUrl, string $cancelUrl): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Stripe API anahtarı (.env içerisinde STRIPE_SECRET) henüz tanımlanmamış.',
                'url' => null,
            ];
        }

        try {
            Stripe::setApiKey($this->secretKey);

            $unitAmountCents = (int) round(($order->total_amount / max(1, $order->quantity)) * 100);

            $session = StripeSession::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => strtolower($order->currency ?: 'eur'),
                        'product_data' => [
                            'name' => $order->product_name . ($order->variant_label ? ' - ' . $order->variant_label : ''),
                            'description' => "Rezervasyon Tarihi: {$order->service_date} | Müşteri: {$order->customer_first_name} {$order->customer_last_name}",
                        ],
                        'unit_amount' => $unitAmountCents,
                    ],
                    'quantity' => $order->quantity,
                ]],
                'mode' => 'payment',
                'customer_email' => $order->customer_email,
                'client_reference_id' => $order->order_no,
                'metadata' => [
                    'order_id' => (string) $order->id,
                    'order_no' => $order->order_no,
                    'iwc_product_id' => (string) $order->iwc_product_id,
                    'service_date' => $order->service_date,
                ],
                'success_url' => $successUrl . (str_contains($successUrl, '?') ? '&' : '?') . 'session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $cancelUrl,
            ]);

            return [
                'success' => true,
                'session_id' => $session->id,
                'url' => $session->url,
            ];
        } catch (\Throwable $e) {
            Log::error('Stripe createCheckoutSession error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Stripe ödeme oturumu başlatılamadı: ' . $e->getMessage(),
                'url' => null,
            ];
        }
    }

    /**
     * Retrieve a Checkout Session from Stripe to verify payment
     */
    public function retrieveSession(string $sessionId): ?StripeSession
    {
        if (!$this->isConfigured()) return null;

        try {
            Stripe::setApiKey($this->secretKey);
            return StripeSession::retrieve($sessionId);
        } catch (\Throwable $e) {
            Log::error('Stripe retrieveSession error: ' . $e->getMessage());
            return null;
        }
    }
}
