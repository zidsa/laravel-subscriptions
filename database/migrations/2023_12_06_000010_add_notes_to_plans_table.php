<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNotesToPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table(config('rinvex.subscriptions.tables.app_market_plans'), function (Blueprint $table) {
            $table->string('note', 1000)->after('description')->nullable();
        });
    }
}
