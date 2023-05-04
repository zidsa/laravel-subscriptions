<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlanOffersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(config('rinvex.subscriptions.tables.app_market_plan_offers'), function (Blueprint $table) {
            $table->increments('id');
            $table->integer('app_id')->unsigned();
            $table->integer('plan_id')->unsigned();
            $table->uuid('purchasable_id');
            $table->json('name')->nullable();
            $table->json('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->decimal('price')->default('0.00');
            $table->smallInteger('offer_period')->unsigned()->default(0);
            $table->string('offer_interval')->default('day');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(config('rinvex.subscriptions.tables.app_market_plan_offers'));
    }
}
