<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'notify_expired_medicines')) {
                $table->boolean('notify_expired_medicines')->default(true)->after('pharmacy_dashboard_notifications');
            }
            if (!Schema::hasColumn('settings', 'notify_low_stock_medicines')) {
                $table->boolean('notify_low_stock_medicines')->default(true)->after('notify_expired_medicines');
            }
            if (!Schema::hasColumn('settings', 'low_stock_threshold')) {
                $table->integer('low_stock_threshold')->default(10)->after('notify_low_stock_medicines');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'low_stock_threshold')) {
                $table->dropColumn('low_stock_threshold');
            }
            if (Schema::hasColumn('settings', 'notify_low_stock_medicines')) {
                $table->dropColumn('notify_low_stock_medicines');
            }
            if (Schema::hasColumn('settings', 'notify_expired_medicines')) {
                $table->dropColumn('notify_expired_medicines');
            }
        });
    }
};
