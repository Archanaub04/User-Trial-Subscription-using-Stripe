<?php

namespace App\Http\Controllers;

use App\Helpers\SubscriptionHelper;
use App\Services\StripeService;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Exception;

class SubscriptionController extends Controller
{
    protected $subscriptionService;
    protected $stripeService;

    public function __construct(SubscriptionService $subscriptionService, StripeService $stripeService)
    {
        $this->subscriptionService = $subscriptionService;
        $this->stripeService = $stripeService;
    }

    public function loadSubscription()
    {
        $plans = $this->subscriptionService->getActivePlans();
        return view('subscription', compact('plans'));
    }

    public function getPlanDetails(Request $request)
    {
        $result = $this->subscriptionService->checkTrialDays($request->id, auth()->id());
        return response()->json($result);
    }

    public function createSubscription(Request $request)
    {
        $result = $this->subscriptionService->createSubscription($request->planID, $request->data, auth()->user());
        return response()->json($result);
    }

    // cancel subscription
    public function cancelSubscription()
    {
        try {
            $subscriptionDetail = SubscriptionHelper::get_current_subscription();

            $this->subscriptionService->cancel_current_subscription(auth()->user()->id, $subscriptionDetail);

            return response()->json(['success' => true, 'message' => 'Subscription cancelled successfully']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // stripe subscription webhook
    public function webhookSubscription()
    {
        $event = $this->stripeService->stripeWebhookVerification();
        $this->subscriptionService->stripeWebhookHandle($event);
        http_response_code(200);
    }
}
