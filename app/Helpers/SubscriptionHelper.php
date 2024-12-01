<?php

namespace App\Helpers;

use App\Models\SubscriptionDetail;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;

class SubscriptionHelper
{

    public static function getSubscriptionInterval($type)
    {
        return match ($type) {
            0 => 'month',
            1 => 'year',
            2 => 'lifetime',
            default => throw new Exception("Invalid subscription type")
        };
    }

    public static function handleException($e)
    {
        return ['success' => false, 'message' => $e->getMessage()];
    }

    public static function subscriptionDatas($user_id, $subscription_id, $customer_ID, $plan_ID, $plan_amount, $planCurrency, $interval, $plan_interval_count, $created, $plan_period_start, $plan_period_end, $trial_end)
    {
        Log::info($plan_period_end);
        return [
            'user_id'                         => $user_id,
            'stripe_subscription_id'          => $subscription_id,
            'stripe_subscription_schedule_id' => "",
            'stripe_customer_id'              => $customer_ID,
            'subscription_plan_price_id'      => $plan_ID,
            'plan_amount'                     => $plan_amount,
            'plan_amount_currency'            => $planCurrency,
            'plan_interval'                   => $interval,
            'plan_interval_count'             => $plan_interval_count,
            'created'                         => $created,
            'plan_period_start'               => $plan_period_start,
            'plan_period_end'                 => $plan_period_end,
            'trial_end'                       => $trial_end,
            'status'                          => 'active',
            'created_at'                      => now(),
            'updated_at'                      => now(),
        ];
    }

    public static function get_current_subscription()
    {
        $current_subscription = SubscriptionDetail::activeForUser(auth()->user()->id)
            ->orderBy('id', 'desc')
            ->first();

        return $current_subscription;
    }

    public static function formatToTimestamp($days)
    {
        return date('Y-m-d H:i:s', $days);
    }

    public static function cancelSubscriptionFromTable($subscription)
    {
        $subscriptiondetail = SubscriptionDetail::where('stripe_subscription_id', $subscription->id)->first();
        SubscriptionDetail::cancelSubscription($subscription->id);
        User::markAsUnSubscribed($subscriptiondetail->user_id);
    }
}
