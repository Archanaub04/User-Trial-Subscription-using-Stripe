<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('subscription_details', function (Blueprint $table) {
            $table->datetime('plan_period_end')->nullable()->change();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_details', function (Blueprint $table) {
            $table->timestamp('plan_period_end')->nullable()->change();
        });
    }
};
