<?php

namespace Database\Seeders;

use Artisan;
use Database\Seeders\Traits\TruncateTable;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder
{
    use TruncateTable;

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->truncateMultiple([
            'cache',
            'jobs',
            'sessions',
        ]);

        $this->call(LocaleSeeder::class);
        $this->call(AuthTableSeeder::class);
        $this->call(PageSeeder::class);
        $this->call(ConfigSeeder::class);
        $this->call(SliderSeeder::class);
        $this->call(MenuSeeder::class);
        $this->call(CommissionRateSeeder::class);
        $this->call(EventsSeeder::class);
        $this->call(NewsSeeder::class);
        $this->call(KpiTypeSeeder::class);
        $this->call(KpiSeeder::class);
        $this->call(KpiLifecycleSeeder::class);
        $this->call(LmsEventCaptureSeeder::class);

        // Artisan::call('translations:import');

        if (! file_exists(public_path('storage'))) {
            Artisan::call('storage:link');
        }

        Model::reguard();
    }
}