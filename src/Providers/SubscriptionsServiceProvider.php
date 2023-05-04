<?php

declare(strict_types=1);

namespace Rinvex\Subscriptions\Providers;

use Rinvex\Subscriptions\Models\AppMarketPlan;
use Illuminate\Support\ServiceProvider;
use Rinvex\Subscriptions\Models\AppMarketPlanOffers;
use Rinvex\Support\Traits\ConsoleTools;
use Rinvex\Subscriptions\Models\AppMarketPlanFeature;
use Rinvex\Subscriptions\Models\AppMarketPlanSubscription;
use Rinvex\Subscriptions\Models\AppMarketPlanSubscriptionUsage;
use Rinvex\Subscriptions\Console\Commands\MigrateCommand;
use Rinvex\Subscriptions\Console\Commands\PublishCommand;
use Rinvex\Subscriptions\Console\Commands\RollbackCommand;

class SubscriptionsServiceProvider extends ServiceProvider
{
    use ConsoleTools;

    /**
     * The commands to be registered.
     *
     * @var array
     */
    protected $commands = [
        MigrateCommand::class => 'command.rinvex.subscriptions.migrate',
        PublishCommand::class => 'command.rinvex.subscriptions.publish',
        RollbackCommand::class => 'command.rinvex.subscriptions.rollback',
    ];

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(realpath(__DIR__.'/../../config/config.php'), 'rinvex.subscriptions');

        // Bind eloquent models to IoC container
        $this->registerModels([
            'rinvex.subscriptions.app_market_plan' => AppMarketPlan::class,
            'rinvex.subscriptions.app_market_plan_feature' => AppMarketPlanFeature::class,
            'rinvex.subscriptions.app_market_plan_subscription' => AppMarketPlanSubscription::class,
            'rinvex.subscriptions.app_market_plan_subscription_usage' => AppMarketPlanSubscriptionUsage::class,
            'rinvex.subscriptions.app_market_plan_offers' => AppMarketPlanOffers::class,
        ]);

        // Register console commands
        $this->registerCommands($this->commands);
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Publish Resources
        $this->publishesConfig('rinvex/laravel-subscriptions');
        $this->publishesMigrations('rinvex/laravel-subscriptions');
        ! $this->autoloadMigrations('rinvex/laravel-subscriptions') || $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }
}
