<?php

declare(strict_types=1);

namespace Rinvex\Subscriptions\Traits;

use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use Rinvex\Subscriptions\Models\AppMarketPlan;
use Rinvex\Subscriptions\Models\AppMarketPlanOffers;
use Rinvex\Subscriptions\Services\Period;
use Illuminate\Database\Eloquent\Collection;
use Rinvex\Subscriptions\Models\AppMarketPlanSubscription;
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
        return $this->morphMany(config('rinvex.subscriptions.models.app_market_plan_subscription'), 'subscriber', 'subscriber_type', 'subscriber_id');
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
     * @return \Rinvex\Subscriptions\Models\AppMarketPlanSubscription|null
     */
    public function subscription(string $subscriptionSlug): ?AppMarketPlanSubscription
    {
        return $this->subscriptions()->where('slug', $subscriptionSlug)->first();
    }

    /**
     * Get subscribed plans.
     *
     * @return \Rinvex\Subscriptions\Models\AppMarketPlanSubscription|null
     */
    public function subscribedPlans(): ?AppMarketPlanSubscription
    {
        $planIds = $this->subscriptions->reject->inactive()->pluck('plan_id')->unique();

        return app('rinvex.subscriptions.app_market_plan')->whereIn('id', $planIds)->get();
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
     * @param \Rinvex\Subscriptions\Models\AppMarketPlan $plan
     * @param \Carbon\Carbon|null               $startDate
     *
     * @return \Rinvex\Subscriptions\Models\AppMarketPlanSubscription
     */
    public function newSubscription($purchaseId, $storeUUid, $subscription, AppMarketPlan $plan, Carbon $startDate = null, $status, $isRecurring = false, $remainingDays = 0, $tax_percentage = 0.15): AppMarketPlanSubscription
    {
        $trial = new Period($plan->trial_interval, $plan->trial_period - 1 , $startDate ?? now());
        $period = new Period($plan->invoice_interval, $plan->invoice_period - 1, $trial->getEndDate());

        $uuid = Uuid::uuid4()->toString();

        return $this->subscriptions()->create([
            'name' => $subscription,
            'uuid' => $uuid,
            'slug' => $uuid,
            'store_uuid' => $storeUUid,
            'plan_id' => $plan->getKey(),
            'app_id' => $plan->getAppId(),
            'status' => $status,
            'is_recurring' => $isRecurring,
            'amount_left' => $plan->price > 0 ? $plan->price * 100 + ($plan->price * $tax_percentage * 100) : 0,
            'amount_left_without_tax' => $plan->price * 100,
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
     * @param \Rinvex\Subscriptions\Models\AppMarketPlan $plan
     * @param \Carbon\Carbon|null               $startDate
     *
     * @return \Rinvex\Subscriptions\Models\AppMarketPlanSubscription
     */
    public function newSubscriptionWithoutTrial($purchaseId, $storeUUid, $subscription, AppMarketPlan $plan, Carbon $startDate = null, $status, $isRecurring = false, $remainingDays = 0, $tax_percentage = 0.15, $activateOffer = true): AppMarketPlanSubscription
    {
        $period = new Period($plan->invoice_interval, $plan->invoice_period - 1, $startDate ?? now());

        /* Auto apply plan offer by default it is true */
        /* @var AppMarketPlanOffers $planOffer */
        $planOffer = $plan->offers()->first();
        $endDate = $period->getEndDate()->addDays($remainingDays);

        if($planOffer && $activateOffer) {
            $endDate = $endDate->addDays($planOffer->getOfferPeriod());
        }

        return $this->subscriptions()->create([
            'name' => $subscription,
            'uuid' => Uuid::uuid4()->toString(),
            'store_uuid' => $storeUUid,
            'plan_id' => $plan->getKey(),
            'app_id' => $plan->getAppId(),
            'status' => $status,
            'is_recurring' => $isRecurring,
            'amount_left' => $plan->price > 0 ? $plan->price * 100 + ($plan->price * $tax_percentage * 100) : 0,
            'amount_left_without_tax' => $plan->price * 100,
            'starts_at' => $period->getStartDate(),
            'ends_at' => $endDate,
            'purchase_id' => $purchaseId,
        ]);
    }


    /**
     * Subscribe subscriber to a new free trial plan.
     *
     * @param string                            $subscription
     * @param \Rinvex\Subscriptions\Models\AppMarketPlan $plan
     * @param \Carbon\Carbon|null               $startDate
     *
     * @return \Rinvex\Subscriptions\Models\AppMarketPlanSubscription
     */
    public function activateFreeTrial($purchaseId, $storeUUid, $subscription, AppMarketPlan $plan, Carbon $startDate = null, $status, $isRecurring = false, $remainingDays = 0): AppMarketPlanSubscription
    {
        $trial = new Period($plan->trial_interval, $plan->trial_period - 1, $startDate ?? now());

        return $this->subscriptions()->create([
            'name' => $subscription,
            'uuid' => Uuid::uuid4()->toString(),
            'store_uuid' => $storeUUid,
            'plan_id' => $plan->getKey(),
            'app_id' => $plan->getAppId(),
            'status' => $status,
            'is_recurring' => $isRecurring,
            'amount_left' => 0,
            'amount_left_without_tax' => 0,
            'trial_ends_at' => $trial->getEndDate(),
            'starts_at' => $trial->getStartDate(),
            'ends_at' => $trial->getEndDate()->addDay($remainingDays),
            'purchase_id' => $purchaseId,
        ]);
    }

}
