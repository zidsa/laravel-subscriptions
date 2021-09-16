<?php

declare(strict_types=1);

return [

    // Manage autoload migrations
    'autoload_migrations' => true,

    // Subscriptions Database Tables
    'tables' => [
        'app_market_plans' => 'app_market_plans',
        'app_market_plan_features' => 'app_market_plan_features',
        'app_market_plan_subscriptions' => 'app_market_plan_subscriptions',
        'app_market_plan_subscription_usage' => 'app_market_plan_subscription_usage',
    ],

    // Subscriptions Models
    'models' => [

        'app_market_plan' => \Rinvex\Subscriptions\Models\AppMarketPlan::class,
        'app_market_plan_feature' => \Rinvex\Subscriptions\Models\AppMarketPlanFeature::class,
        'app_market_plan_subscription' => \Rinvex\Subscriptions\Models\AppMarketPlanSubscription::class,
        'app_market_plan_subscription_usage' => \Rinvex\Subscriptions\Models\AppMarketPlanSubscriptionUsage::class,

    ],

];
