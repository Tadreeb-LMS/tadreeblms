<?php

namespace Database\Seeders;

use App\Models\KpiType;
use Illuminate\Database\Seeder;

class KpiTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $rows = [
            [
                'key' => 'completion',
                'label' => 'Completion',
                'description' => 'Measures completion progress as a percentage.',
                'is_active' => true,
                'sort_order' => 10,
            ],
            [
                'key' => 'score',
                'label' => 'Score',
                'description' => 'Measures result quality based on score outcomes.',
                'is_active' => true,
                'sort_order' => 20,
            ],
            [
                'key' => 'activity',
                'label' => 'Activity',
                'description' => 'Measures engagement/activity from platform interactions.',
                'is_active' => true,
                'sort_order' => 30,
            ],
            [
                'key' => 'time',
                'label' => 'Time',
                'description' => 'Measures time-based performance against expected duration.',
                'is_active' => true,
                'sort_order' => 40,
            ],
        ];

        foreach ($rows as $row) {
            KpiType::query()->updateOrCreate(
                ['key' => $row['key']],
                $row
            );
        }
    }
}
