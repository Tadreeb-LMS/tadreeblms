<?php

namespace Database\Seeders;

use App\Models\Auth\User;
use App\Models\Kpi;
use Illuminate\Database\Seeder;

class KpiLifecycleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $seedUserId = User::query()->value('id');

        $legacy = Kpi::withTrashed()->updateOrCreate(
            ['code' => 'LEGACY_SUPPORT_RESPONSE_TIME'],
            [
                'name' => 'Legacy Support Response Time',
                'type' => 'time',
                'description' => 'Legacy KPI kept for historical reporting validation in tests.',
                'weight' => 1,
                'is_active' => false,
                'created_by' => $seedUserId,
                'updated_by' => $seedUserId,
            ]
        );

        if (!$legacy->trashed()) {
            $legacy->delete();
        }

        $inactive = Kpi::query()->where('code', 'TRAINING_ATTENDANCE_RATE')->first();
        if ($inactive) {
            $inactive->is_active = false;
            $inactive->updated_by = $seedUserId;
            $inactive->save();
        }
    }
}
