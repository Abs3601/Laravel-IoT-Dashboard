<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->string('device_group')->nullable()->after('entity_id');
            $table->index('device_group');
        });

        // Backfill existing devices with device_group
        $this->backfillDeviceGroups();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropIndex(['device_group']);
            $table->dropColumn('device_group');
        });
    }

    /**
     * Backfill device_group for existing devices.
     */
    private function backfillDeviceGroups(): void
    {
        $suffixes = [
            '_voltage',
            '_current',
            '_power',
            '_signal_level',
            '_current_consumption',
            '_today_s_consumption',
            '_this_month_s_consumption',
            '_summation_delivered',
            '_auto_off_at',
            '_auto_off_enabled',
            '_led',
            '_cloud_connection',
            '_overheated',
            '_firmware',
            '_turn_off_in',
            '_start_up_behaviour',
            '_identify',
        ];

        \App\Models\Device::chunk(100, function ($devices) use ($suffixes) {
            foreach ($devices as $device) {
                $entityId = $device->entity_id;
                $deviceGroup = $entityId;

                foreach ($suffixes as $suffix) {
                    if (str_ends_with($entityId, $suffix)) {
                        $deviceGroup = str_replace($suffix, '', $entityId);
                        break;
                    }
                }

                $device->update(['device_group' => $deviceGroup]);
            }
        });
    }
};
