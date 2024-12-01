<?php

namespace App\Repositories;

use App\Interfaces\SubscriptionRepositoryInterface;
use App\Models\SubscriptionDetail;
use App\Models\SubscriptionPlan;

class SubscriptionRepository implements SubscriptionRepositoryInterface
{
    public function getActivePlans()
    {
        return SubscriptionPlan::where('enabled', 1)->get();
    }

    public function getPlanDetails($planId)
    {
        return SubscriptionPlan::find($planId);
    }
    // insert subscription data
    public function storeSubscriptionDetail($data)
    {
        return SubscriptionDetail::insert($data);
    }

    // update or create subscription data
    public function updateOrCreateSubscriptionDetail($user_id, $customer_id, $subscriptionPlan, $subscriptionDetailsData)
    {
        return SubscriptionDetail::updateOrCreate(
            [
                'user_id'            => $user_id,
                'stripe_customer_id' => $customer_id,
                'subscription_plan_price_id' => $subscriptionPlan->stripe_price_id,
            ],
            $subscriptionDetailsData
        );
    }

    public function updateSubscriptionDetail($subscriptionID, $subscriptionData)
    {
        return SubscriptionDetail::subscriptionByID($subscriptionID)->update($subscriptionData);
    }
}
