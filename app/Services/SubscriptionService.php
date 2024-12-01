<?php

namespace App\Services;

use App\Helpers\SubscriptionHelper;
use App\Interfaces\SubscriptionRepositoryInterface;
use App\Models\PendingFees;
use App\Models\SubscriptionDetail;
use App\Models\User;
use App\Repositories\CardRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

class SubscriptionService
{
    protected $subscriptionRepository;
    protected $cardRepository;
    protected $stripeService;

    public function __construct(SubscriptionRepositoryInterface $subscriptionRepository, CardRepository $cardRepository, StripeService $stripeService)
    {
        $this->subscriptionRepository = $subscriptionRepository;
        $this->cardRepository = $cardRepository;
        $this->stripeService = $stripeService;
    }

    public function getActivePlans()
    {
        return $this->subscriptionRepository->getActivePlans();
    }

    public function checkTrialDays($planId, $userId)
    {
        try {
            $planData = $this->subscriptionRepository->getPlanDetails($planId);
            $activeSubscription = SubscriptionDetail::activeForUser($userId)->exists();

            $message = $activeSubscription
                ? "We will charge \${$planData->amount} for {$planData->name} Subscription Plan."
                : "You will get {$planData->trial_days} days free trial, after which we will charge \${$planData->amount} for {$planData->name} Subscription Plan.";

            return ['success' => true, 'message' => $message, 'data' => $planData];
        } catch (Exception $e) {
            return SubscriptionHelper::handleException($e);
        }
    }
    public function createSubscription($planID, $stripeData, $user)
    {
        try {
            // Get or create Stripe customer
            $customer = $this->stripeService->getOrCreateCustomer($user, $stripeData);
            // get plan details
            $subscriptionPlan = $this->subscriptionRepository->getPlanDetails($planID);
            // check if the user exists any current active subscription
            $subscriptionDetail = SubscriptionDetail::activeForUser($user->id)->first();
            // check if any subecription available for the user
            $subscriptionDetailCount = SubscriptionDetail::byUserId($user->id)->count();

            /* ---- Upgrade & Downgrade Subscription ---- */
            // Manage new or existing subscription
            $subscriptionData = $subscriptionDetail
                ? $this->handleUpgradeDowngrade($subscriptionDetail, $subscriptionPlan, $user, $customer)
                : $this->handleNewSubscription($subscriptionDetailCount, $customer, $subscriptionPlan, $user);
            /* ------------------------------------------ */
            // Store card details
            $this->storeCardDetails($stripeData, $user->id, $customer);

            return $subscriptionData
                ? ['success' => true, 'message' => 'Subscription Purchased!']
                : ['success' => false, 'message' => 'Failed to create subscription.'];
        } catch (Exception $e) {
            return SubscriptionHelper::handleException($e);
        }
    }

    private function handleUpgradeDowngrade($subscriptionDetail, $subscriptionPlan, $user, $customer)
    {
        if ($subscriptionDetail) {
            $newplan_interval = SubscriptionHelper::getSubscriptionInterval($subscriptionPlan->type);
            // cancel current subscription
            $this->cancel_current_subscription($user->id, $subscriptionDetail);
            $subscriptionData = $this->start_subscription($customer, $user->id, auth()->user()->name, $subscriptionPlan, $newplan_interval, "start", $subsID = null);

            return $subscriptionData;
        }
    }

    private function handleNewSubscription($subscriptionDetailCount, $customer, $subscriptionPlan, $user)
    {
        $interval = SubscriptionHelper::getSubscriptionInterval($subscriptionPlan->type);
        if ($subscriptionDetailCount === 0) {
            // First-time subscription or new user
            $subscriptionData = $this->start_trial_subscription($customer, $user->id, $subscriptionPlan, $interval);
        } else {
            // Old user with all subscriptions canceled
            // For existing users without active subscriptions

            /* for capture pending fees when subscribing in middle of month.

             * In stripe, either monthly / yearly when subscription creates in middle of month then from
               middle to end of that month stripe take full amount.
             * Means stripe plan is not from March 1 to april 1.
             * march 15 to april 1 also tae same amount. same way in years also.
             * so to avoid capture the pending days (means from start to when we start subscription) and
               calculate it amount.
            */
            if ($interval == "month" || $interval == "year") {
                $this->capture_pending_fees($customer, $user->id, auth()->user()->name, $subscriptionPlan, $interval);
            }
            $subscriptionData = $this->start_subscription($customer, $user->id, auth()->user()->name, $subscriptionPlan, $interval, "start", $subsID = null);
        }
        return $subscriptionData;
    }

    public function start_trial_subscription($customer_id, $user_id, $subscriptionPlan, $interval)
    {
        try {
            $Date = date('Y-m-d 23:59:59');
            $trialDays = strtotime($Date . '+' . $subscriptionPlan->trial_days . ' days');
            $plan_period_start = $created = now();
            $plan_period_end = SubscriptionHelper::formatToTimestamp($trialDays);
            $trial_end = $trialDays;

            $subscriptionDetailsData = SubscriptionHelper::subscriptionDatas($user_id, NULL, $customer_id, $subscriptionPlan->stripe_price_id, $subscriptionPlan->amount, 'usd', $interval, 1, $created, $plan_period_start, $plan_period_end, $trial_end);

            $stripeData = $this->subscriptionRepository->updateOrCreateSubscriptionDetail($user_id, $customer_id, $subscriptionPlan, $subscriptionDetailsData);

            User::markAsSubscribed($user_id);
            return $stripeData;
        } catch (Exception $e) {
            Log::info($e->getMessage());
            return null;
        }
    }

    public function capture_pending_fees($customer_id, $user_id, $user_name, $subscriptionPlan, $interval)
    {
        try {
            $totalAmount = $subscriptionPlan->amount;

            if ($interval === "month") {
                $daysInMonth = date('t'); // total days in a month like  - 31
                $currentDay = date('j'); // current days in month like  - 1st day, 2nd day

                $amountForRestDays = ceil(($daysInMonth - $currentDay) * ($totalAmount / $daysInMonth)); // (31 - 1) * (amount / 31)
                $amountForRest = $amountForRestDays;

                $description = "Monthly Pending Fee";
            } else if ($interval === "year") {
                // $monthsInYear = 12;
                $currentMonth = date('m') - 1; // if november then 11 - 1 = 10 so 10 months passed
                $amountForRestMonths = ceil((12 - $currentMonth) * ($totalAmount / 12)); // (12 - 10) * (amount / 12) = 2 * (amount /12)
                $amountForRest = $amountForRestMonths;

                $description = "Yearly Pending Fees";
            }

            // Get or create Stripe customer
            $stripeChargeData = $this->stripeService->createStripeCharge($amountForRest, $customer_id, $user_name, $description);

            // insert data to pending fee table
            if (!empty($stripeChargeData)) {
                $stripeCharge = $stripeChargeData->jsonSerialize(); // convert complete data structure (json) to arrat so in easily storable format.
                $pendingFeeData = [
                    'user_id' => $user_id,
                    'charge_id' => $stripeCharge['id'],
                    'customer_id' => $stripeCharge['customer'],
                    'amount' => $amountForRest,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
                PendingFees::insertData($pendingFeeData);
            }
            // END ----------------------------------------------------
        } catch (Exception $e) {
            Log::info($e->getMessage());
            return null;
        }
    }
    //  start new subscription
    public function start_subscription($customer_id, $user_id, $user_name, $subscriptionPlan, $interval, $subs_type, $subsID = null)
    {
        try {
            if ($interval == "month") {
                /* subscription start from next month start - 01
                *  so take 1st of current month.
                   strtotime() for milli seconds date - "1733011200" - son to format it use date('Y-m-d)
                */
                $currentMonthDate = strtotime(date('Y-m-') . '01'); // 2024-11-01
                // get next month 1st data 2024-12-01
                $current_period_start = date("Y-m-d", strtotime("+1 month", $currentMonthDate)) . ' 00:00:00';
                /* t provide months last date  - current date + 1 month and its last data and 23:5:59
                   2024-12-31 23:59:59 */
                $current_period_end = date("Y-m-t", strtotime("+1 month")) . ' 23:59:59';
            } else if ($interval == "year") {
                $current_period_start = date('Y-', strtotime('+1 year')) . '01-01 00:00:00'; // next year januvary 1
                $current_period_end = date('Y-', strtotime('+1 year')) . '12-31 23:59:59'; // that year last month last date - december 31.
            } else if ($interval == "lifetime") {
                $current_period_start = now();
                $month = date('m-d');
                $current_period_end = '2099-' . $month . ' 23:59:59';
            }

            if ($interval == "month" || $interval == "year") {
                // Create subscription
                $stripeData = $this->stripeService->createSubscription($customer_id, $subscriptionPlan, $current_period_start);
            } else if ($interval == "lifetime") {
                // Get or create Stripe customer
                $stripeData = $this->stripeService->createStripeCharge($subscriptionPlan->amount, $customer_id, $user_name, 'Lifetime Subscription Buy');
            }

            if (!empty($stripeData)) {
                $stripeData = $stripeData->jsonSerialize();

                $subscriptionID = $stripeData['id'];
                $customerID = $stripeData['customer'];
                $created = SubscriptionHelper::formatToTimestamp($stripeData['created']);

                if ($interval == "month" || $interval == "year") {

                    $planID = $stripeData['items'] ? $stripeData['items']['data'][0]['plan']['id'] : $stripeData['plan']['id'];

                    // Retrieve plan details from price based on the subscribed plan
                    $priceData = $this->stripeService->retrievePlanDetails($planID);

                    $planAmount = $priceData->amount / 100;
                    $planCurrency = $priceData->currency;
                    $planInterval = $priceData->interval;
                    $planIntervalCount = $priceData->interval_count;
                } else if ($interval == "lifetime") {
                    $planID = $subscriptionPlan->stripe_price_id;
                    $planAmount = $subscriptionPlan->amount;
                    $planCurrency = $stripeData['currency'];
                    $planInterval = $interval;
                    $planIntervalCount = 1;
                }

                $subscriptionDetailsData = SubscriptionHelper::subscriptionDatas($user_id, $subscriptionID, $customerID, $planID, $planAmount, $planCurrency, $planInterval, $planIntervalCount, $created, $current_period_start, $current_period_end, NULL);

                if ($subs_type == "start") {
                    $stripeData = $this->subscriptionRepository->storeSubscriptionDetail($subscriptionDetailsData);
                } else if ($subs_type == "renew") {
                    $stripeData = $this->subscriptionRepository->updateSubscriptionDetail($subsID, $subscriptionDetailsData);
                }
                User::markAsSubscribed($user_id);
            }

            return $stripeData;
        } catch (Exception $e) {
            Log::info($e->getMessage());
            return null;
        }
    }

    private function storeCardDetails($stripeData, $userId, $customerId)
    {
        return $this->cardRepository->storeCardDetails([
            'user_id' => $userId,
            'customer_id' => $customerId,
            'card_id' => $stripeData['card']['id'],
            'name' => $stripeData['card']['name'],
            'card_no' => $stripeData['card']['last4'],
            'brand' => $stripeData['card']['brand'],
            'month' => $stripeData['card']['exp_month'],
            'year' => $stripeData['card']['exp_year'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    // ---------------------------------------------------------------------

    // cancel current subscription
    public function cancel_current_subscription($user_id, $subscriptionDetail)
    {
        try {
            $stripe_subscription_ID = $subscriptionDetail->stripe_subscription_id;
            // not for trial. only cancel month or year
            if ($stripe_subscription_ID != null && $stripe_subscription_ID != '') {
                $this->stripeService->cancelSubscription($stripe_subscription_ID);
            }
            //  for trial cancel
            SubscriptionDetail::cancelSubscription($subscriptionDetail->id);

            User::markAsUnSubscribed($user_id);
        } catch (Exception $e) {
            Log::info($e->getMessage());
        }
    }

    /* --------------- Renew Subscription ------------------ */
    public function renew_subscription($subscriptionDetail, $user_id, $user_name, $subscriptionPlan, $interval, $subscriptionID)
    {
        try {
            $this->start_subscription($subscriptionDetail->stripe_customer_id, $user_id, $user_name, $subscriptionPlan, $interval, "renew", $subscriptionID);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            return null;
        }
    }
    /* ----------------------------------------------------- */

    // stripe webhook handle
    public function stripeWebhookHandle($event)
    {
        switch ($event->type) {
            case 'customer.subscription.deleted':
                $subscription = $event->data->object;
                SubscriptionHelper::cancelSubscriptionFromTable($subscription);

                break;
            case 'customer.subscription.paused':
                $subscription = $event->data->object;
                SubscriptionHelper::cancelSubscriptionFromTable($subscription);

                break;
            case 'customer.subscription.resumed':
                $subscription = $event->data->object;
                SubscriptionHelper::cancelSubscriptionFromTable($subscription);

                break;
            case 'invoice.payment_succeeded': //subscription renewal event
                // new renewed subscription id
                $stripeSubscriptionId = $event->data->object->subscription;

                if ($stripeSubscriptionId) {
                    $stripeSubscription = $this->stripeService->retrieveSubscriptionData($stripeSubscriptionId);

                    $this->handleSubscriptionPaid($stripeSubscription);
                }

                break;
            case 'subscription_schedule.resumed':
                $subscriptionSchedule = $event->data->object;
                break;
            case 'subscription_schedule.canceled':
                $subscriptionSchedule = $event->data->object;
                break;
            case 'customer.subscription.created':
                break;
            default:
                // Unexpected event type
                error_log('Received unknown event type' . $event->type);
        }
    }

    public function handleSubscriptionPaid($stripeSubscription)
    {
        $newPeriodEnd = $stripeSubscription->current_period_end;
        $subscriptionDetail = SubscriptionDetail::where('stripe_subscription_id', $stripeSubscription->id)->first();

        if ($subscriptionDetail) {
            $user_id = $subscriptionDetail->user_id;
            if ($stripeSubscription->id == $subscriptionDetail->stripe_subcription_id) {
                // convert to milliseconds because stripe always send date in milliseconds
                // check if subscription laready renewed
                // if renewed then the date plan_period_end current will be greater
                $isRenewal = $newPeriodEnd > strtotime($subscriptionDetail->plan_period_end);

                if ($isRenewal) {
                    $apiError = '';
                    try {
                        $stripeSubscription = $this->stripeService->retrieveSubscriptionData($subscriptionDetail->stripe_subcription_id);
                    } catch (Exception $e) {
                        $apiError = $e->getMessage();
                    }

                    if (empty($apiError) && $stripeSubscription) {
                        $subscriptionData = $stripeSubscription->jsonSerialize();

                        SubscriptionDetail::where('user_id', $user_id)->update([
                            'stripe_subscription_id' => $subscriptionData->id,
                            'plan_interval_count' => $subscriptionData['plan']['interval_count'],
                            'plan_peroid_end' => date('Y-m-d H:i:s', $newPeriodEnd),
                        ]);
                    } else {
                        Log::info($apiError);
                    }
                }
            }
        }
    }
}
