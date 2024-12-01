<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //

        $currentDateTime = Carbon::now()->format('Y-m-d H:i:s');

        SubscriptionPlan::insert([
            [
                'name' => 'Monthly',
                'stripe_price_id' => 'price_1Q4Nd7J51P8ivkuNQh90H9cb',
                'trial_days' => 5,
                'amount' => 12,
                'type' => 0,
                'enabled' => 1,
                'created_at' => $currentDateTime,
                'updated_at' => $currentDateTime
            ],
            [
                'name' => 'Yearly',
                'stripe_price_id' => 'price_1Q4NeEJ51P8ivkuNPBvco7hk',
                'trial_days' => 5,
                'amount' => 100,
                'type' => 1,
                'enabled' => 1,
                'created_at' => $currentDateTime,
                'updated_at' => $currentDateTime
            ],
            [
                'name' => 'LifeTime',
                'stripe_price_id' => 'price_1Q4NfcJ51P8ivkuNR8TgN2qi',
                'trial_days' => 5,
                'amount' => 400,
                'type' => 2,
                'enabled' => 1,
                'created_at' => $currentDateTime,
                'updated_at' => $currentDateTime
            ]
        ]);
    }
}
