<?php

use App\Foundation\Database\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(config('rinvex.subscriptions.tables.app_market_plans'), function (Blueprint $table) {
            $table->boolean('is_usage_based')->default(false);
            $table->json('meta')->nullable();
        });
    }
};
