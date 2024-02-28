<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSignupFeePurchasableIdToPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table(config('rinvex.subscriptions.tables.app_market_plans'), function (Blueprint $table) {
            $table->uuid('signup_fee_purchasable_id')->after('purchasable_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table(config('rinvex.subscriptions.tables.app_market_plans'), function (Blueprint $table) {
            $table->dropColumn('signup_fee_purchasable_id');
        });
    }
}
