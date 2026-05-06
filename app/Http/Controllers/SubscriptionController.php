<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function showPlans(): View
    {
        $plans = config('plans');
        $user  = auth()->user();

        return view('subscription.plans', compact('plans', 'user'));
    }

    public function checkout(Request $request): RedirectResponse|\Laravel\Cashier\Checkout
    {
        $request->validate(['plan' => 'required|in:starter,agency,pro']);

        $planName = $request->input('plan');
        $plan     = config("plans.{$planName}");

        if (!$plan || empty($plan['stripe_price_id'])) {
            return redirect()->route('subscription.plans')
                ->with('error', 'Dieser Plan ist momentan nicht verfügbar.');
        }

        $user = auth()->user();

        return $user->newSubscription('default', $plan['stripe_price_id'])
            ->checkout([
                'success_url'             => route('subscription.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'              => route('subscription.plans'),
                'allow_promotion_codes'   => true,
                'billing_address_collection' => 'auto',
            ]);
    }

    public function success(Request $request): View
    {
        return view('subscription.success');
    }

    public function cancel(): RedirectResponse
    {
        return redirect()->route('subscription.plans')
            ->with('info', 'Checkout abgebrochen. Du kannst jederzeit upgraden.');
    }

    public function billingPortal(Request $request): RedirectResponse
    {
        $url = auth()->user()->billingPortalUrl(route('dashboard'));

        return redirect($url);
    }
}
