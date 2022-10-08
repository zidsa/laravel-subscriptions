<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlanSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(config('rinvex.subscriptions.tables.app_market_plan_subscriptions'), function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('uuid');
            $table->morphs('subscriber', 'subscriber_type_subscriber_id_index');
            $table->uuid('store_uuid');
            $table->integer('plan_id')->unsigned();
            $table->integer('app_id')->unsigned();
            $table->string('slug');
            $table->json('name');
            $table->json('description')->nullable();
            $table->string('status');
            $table->dateTime('trial_ends_at')->nullable();
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->dateTime('cancels_at')->nullable();
            $table->dateTime('canceled_at')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->unsignedInteger('recurring_retry')->unsigned()->default(0);
            $table->string('recurring_status')->nullable()->default(null);
            $table->unsignedInteger('amount_left')->unsigned()->default(0);
            $table->unsignedInteger('amount_left_without_tax')->unsigned()->default(0);
            $table->uuid('purchase_id')->nullable()->default(null);
            $table->string('timezone')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->unique('slug');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(config('rinvex.subscriptions.tables.app_market_plan_subscriptions'));
    }
}
