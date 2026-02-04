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
            if (!Schema::hasColumn('settings', 'notify_pending_prescriptions')) {
                $table->boolean('notify_pending_prescriptions')->default(true)->after('notify_low_stock_medicines');
            }
            if (!Schema::hasColumn('settings', 'notify_dispensed_prescriptions')) {
                $table->boolean('notify_dispensed_prescriptions')->default(true)->after('notify_pending_prescriptions');
            }
            if (!Schema::hasColumn('settings', 'notify_inventory_status')) {
                $table->boolean('notify_inventory_status')->default(true)->after('notify_dispensed_prescriptions');
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
            if (Schema::hasColumn('settings', 'notify_inventory_status')) {
                $table->dropColumn('notify_inventory_status');
            }
            if (Schema::hasColumn('settings', 'notify_dispensed_prescriptions')) {
                $table->dropColumn('notify_dispensed_prescriptions');
            }
            if (Schema::hasColumn('settings', 'notify_pending_prescriptions')) {
                $table->dropColumn('notify_pending_prescriptions');
            }
        });
    }
};
