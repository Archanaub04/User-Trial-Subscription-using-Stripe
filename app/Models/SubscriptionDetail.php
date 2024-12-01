<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'stripe_subscription_id',
        'stripe_subscription_schedule_id',
        'stripe_customer_id',
        'subscription_plan_price_id',
        'plan_amount',
        'plan_amount_currency',
        'plan_interval',
        'plan_interval_count',
        'created',
        'plan_period_start',
        'plan_period_end',
        'trial_end',
        'status',
        'cancel',
        'cancelled_at',
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id'); // id - primary key of linked table and then foreign key of this table
    }
    // Scope to filter by user ID
    public function scopeByUserId($query, $userId)
    {
        return $query->where('user_id', $userId)->orderByCreatedDesc();
    }

    // Scope to filter by active status
    public function scopeActiveStatus($query)
    {
        return $query->where('status', 'active');
    }

    // Scope to filter by cancel status
    public function scopeNotCancelled($query)
    {
        return $query->where('cancel', 0);
    }
    public function scopeOrderByCreatedDesc($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    // Scope to get active subscriptions for a user
    public function scopeActiveForUser($query, $userId)
    {
        return $query->byUserId($userId)
            ->activeStatus()
            ->notCancelled();
    }
    public function scopeSubscriptionByID($query, $subscriptionID)
    {
        return $query->where('id', $subscriptionID);
    }

    public static function cancelSubscription($subscriptionID)
    {
        return self::where('stripe_subscription_id', $subscriptionID)
            ->update([
                'status' => 'cancelled',
                'cancel' => 1,
                'cancelled_at' => now()
            ]);
    }
}
