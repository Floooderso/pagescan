<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierWebhookController;

class WebhookController extends CashierWebhookController
{
    protected function handleCustomerSubscriptionCreated(array $payload): Response
    {
        $this->updateUserScanLimit($payload['data']['object']);

        return parent::handleCustomerSubscriptionCreated($payload);
    }

    protected function handleCustomerSubscriptionUpdated(array $payload): Response
    {
        $this->updateUserScanLimit($payload['data']['object']);

        return parent::handleCustomerSubscriptionUpdated($payload);
    }

    protected function handleCustomerSubscriptionDeleted(array $payload): Response
    {
        $customerId = $payload['data']['object']['customer'] ?? null;
        $user       = $customerId ? User::where('stripe_id', $customerId)->first() : null;

        if ($user) {
            $user->update(['scans_limit' => 10]);
        }

        return parent::handleCustomerSubscriptionDeleted($payload);
    }

    private function updateUserScanLimit(array $subscription): void
    {
        $customerId = $subscription['customer'] ?? null;
        $priceId    = $subscription['items']['data'][0]['price']['id'] ?? null;

        if (!$customerId || !$priceId) {
            return;
        }

        $user = User::where('stripe_id', $customerId)->first();
        if (!$user) {
            return;
        }

        $limit = $this->scanLimitForPrice($priceId);
        $user->update(['scans_limit' => $limit]);
    }

    private function scanLimitForPrice(string $priceId): int
    {
        foreach (config('plans', []) as $plan) {
            if ($plan['stripe_price_id'] === $priceId) {
                return (int) $plan['scans']; // 0 = unlimited
            }
        }

        return 200;
    }
}
