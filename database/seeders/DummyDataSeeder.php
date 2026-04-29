<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(CourseSeed::class);
        $this->call(QuestionsSeed::class);
        $this->call(SliderSeeder::class);
        $this->call(TestimonialSeeder::class);
        $this->call(SponsorSeeder::class);
        $this->call(FaqSeeder::class);
        $this->call(ReasonSeeder::class);
        $this->call(ChatterTableSeeder::class);
        $this->call(KpiSeeder::class);
        $this->call(KpiLifecycleSeeder::class);

    }
}
