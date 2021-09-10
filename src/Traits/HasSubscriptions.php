<?php

declare(strict_types=1);

namespace Rinvex\Subscriptions\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;
use Rinvex\Subscriptions\Models\Plan;
use Rinvex\Subscriptions\Services\Period;
use Illuminate\Database\Eloquent\Collection;
use Rinvex\Subscriptions\Models\PlanSubscription;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasSubscriptions
{
    /**
     * Define a polymorphic one-to-many relationship.
     *
     * @param string $related
     * @param string $name
     * @param string $type
     * @param string $id
     * @param string $localKey
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    abstract public function morphMany($related, $name, $type = null, $id = null, $localKey = null);

    /**
     * The subscriber may have many subscriptions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function subscriptions(): MorphMany
    {
        return $this->morphMany(config('rinvex.subscriptions.models.plan_subscription'), 'subscriber', 'subscriber_type', 'subscriber_id');
    }

    /**
     * A model may have many active subscriptions.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function activeSubscriptions(): Collection
    {
        return $this->subscriptions->reject->inactive();
    }

    /**
     * Get a subscription by slug.
     *
     * @param string $subscriptionSlug
     *
     * @return \Rinvex\Subscriptions\Models\PlanSubscription|null
     */
    public function subscription(string $subscriptionSlug): ?PlanSubscription
    {
        return $this->subscriptions()->where('slug', $subscriptionSlug)->first();
    }

    /**
     * Get subscribed plans.
     *
     * @return \Rinvex\Subscriptions\Models\PlanSubscription|null
     */
    public function subscribedPlans(): ?PlanSubscription
    {
        $planIds = $this->subscriptions->reject->inactive()->pluck('plan_id')->unique();

        return app('rinvex.subscriptions.plan')->whereIn('id', $planIds)->get();
    }

    /**
     * Check if the subscriber subscribed to the given plan.
     *
     * @param int $planId
     *
     * @return bool
     */
    public function subscribedTo($planId): bool
    {
        $subscription = $this->subscriptions()->where('plan_id', $planId)->first();

        return $subscription && $subscription->active();
    }

    /**
     * Subscribe subscriber to a new plan.
     *
     * @param string                            $subscription
     * @param \Rinvex\Subscriptions\Models\Plan $plan
     * @param \Carbon\Carbon|null               $startDate
     *
     * @return \Rinvex\Subscriptions\Models\PlanSubscription
     */
    public function newSubscription($purchaseId, $storeUUid, $subscription, Plan $plan, Carbon $startDate = null, $status, $isRecurring = false, $remainingDays = 0): PlanSubscription
    {
        $trial = new Period($plan->trial_interval, $plan->trial_period - 1 , $startDate ?? now());
        $period = new Period($plan->invoice_interval, $plan->invoice_period - 1, $trial->getEndDate());

        return $this->subscriptions()->create([
            'name' => $subscription,
            'uuid' => Uuid::uuid4()->toString(),
            'store_uuid' => $storeUUid,
            'plan_id' => $plan->getKey(),
            'app_id' => $plan->getAppId(),
            'status' => $status,
            'is_recurring' => $isRecurring,
            'amount_left' => $plan->price > 0 ? $plan->price * 100 : 0,
            'trial_ends_at' => $trial->getEndDate(),
            'starts_at' => $period->getStartDate(),
            'ends_at' => $period->getEndDate()->addDay($remainingDays),
            'purchase_id' => $purchaseId,
        ]);
    }

    /**
     * Subscribe subscriber to a new plan with trial since the user has already subscribed to the trial before
     *
     * @param string                            $subscription
     * @param \Rinvex\Subscriptions\Models\Plan $plan
     * @param \Carbon\Carbon|null               $startDate
     *
     * @return \Rinvex\Subscriptions\Models\PlanSubscription
     */
    public function newSubscriptionWithoutTrial($purchaseId, $storeUUid, $subscription, Plan $plan, Carbon $startDate = null, $status, $isRecurring = false, $remainingDays = 0): PlanSubscription
    {
        $period = new Period($plan->invoice_interval, $plan->invoice_period - 1, $startDate ?? now());

        Log::info($plan->invoice_period);
        Log::info($period->getStartDate());
        Log::info($period->getEndDate());

        return $this->subscriptions()->create([
            'name' => $subscription,
            'uuid' => Uuid::uuid4()->toString(),
            'store_uuid' => $storeUUid,
            'plan_id' => $plan->getKey(),
            'app_id' => $plan->getAppId(),
            'status' => $status,
            'is_recurring' => $isRecurring,
            'amount_left' => $plan->price > 0 ? $plan->price * 100 : 0,
            'starts_at' => $period->getStartDate(),
            'ends_at' => $period->getEndDate()->addDay($remainingDays),
            'purchase_id' => $purchaseId,
        ]);
    }


    /**
     * Subscribe subscriber to a new free trial plan.
     *
     * @param string                            $subscription
     * @param \Rinvex\Subscriptions\Models\Plan $plan
     * @param \Carbon\Carbon|null               $startDate
     *
     * @return \Rinvex\Subscriptions\Models\PlanSubscription
     */
    public function activateFreeTrial($purchaseId, $storeUUid, $subscription, Plan $plan, Carbon $startDate = null, $status, $isRecurring = false, $remainingDays = 0): PlanSubscription
    {
        $trial = new Period($plan->trial_interval, $plan->trial_period - 1, $startDate ?? now());

        Log::info($plan->trial_period);
        Log::info($trial->getStartDate());
        Log::info($trial->getEndDate());

        return $this->subscriptions()->create([
            'name' => $subscription,
            'uuid' => Uuid::uuid4()->toString(),
            'store_uuid' => $storeUUid,
            'plan_id' => $plan->getKey(),
            'app_id' => $plan->getAppId(),
            'status' => $status,
            'is_recurring' => $isRecurring,
            'amount_left' => 0,
            'trial_ends_at' => $trial->getEndDate(),
            'starts_at' => $trial->getStartDate(),
            'ends_at' => $trial->getEndDate()->addDay($remainingDays),
            'purchase_id' => $purchaseId,
        ]);
    }

}
