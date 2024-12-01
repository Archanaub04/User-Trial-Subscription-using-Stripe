<?php

namespace App\Interfaces;

interface SubscriptionRepositoryInterface
{
    public function getActivePlans();
    public function getPlanDetails($planId);
    public function storeSubscriptionDetail($data);
    public function updateOrCreateSubscriptionDetail($user_id, $customer_id, $subscriptionPlan, $subscriptionDetailsData);
    public function updateSubscriptionDetail($subscriptionID, $subscriptionData);
}
