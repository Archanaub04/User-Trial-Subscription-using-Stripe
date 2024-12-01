<?php

namespace App\Console\Commands;

use App\Interfaces\SubscriptionRepositoryInterface;
use App\Models\SubscriptionDetail;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Console\Command;
use Stripe\StripeClient;

class UpdateTrialSubscription extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-trial-subscription';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Trial user subscription into real subscription';

    protected $stripe;
    protected $subscriptionService;
    protected $subscriptionRepository;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->stripe = new StripeClient(env('STRIPE_SECRET_KEY'));
        $this->subscriptionService = $subscriptionService;

        parent::__construct();
    }
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $subscriptionDetails = SubscriptionDetail::with('user')
            ->activeStatus()
            ->notCancelled()
            ->where('plan_period_end', '<', now())
            ->whereNotNull('trial_end')
            ->orderBy('id', 'desc')
            ->get();

        if (count($subscriptionDetails) > 0) {
            foreach ($subscriptionDetails as $detail) {


                $subscriptionPlan = SubscriptionPlan::where('stripe_price_id', $detail->subscription_plan_price_id)->first();

                $this->subscriptionService->capture_pending_fees($detail->stripe_customer_id, $detail->user_id, $detail->user->name, $subscriptionPlan, $detail->plan_interval);

                $this->subscriptionService->renew_subscription(
                    $detail,
                    $detail->user_id,
                    $detail->user->name,
                    $subscriptionPlan,
                    $detail->plan_interval,
                    $detail->id,
                );
            }
        }
    }
}
