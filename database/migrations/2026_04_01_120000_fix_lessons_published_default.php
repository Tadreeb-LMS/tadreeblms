<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixLessonsPublishedDefault extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('lessons')) {
            return;
        }

        // Backfill legacy null values before enforcing default/non-null.
        DB::table('lessons')->whereNull('published')->update(['published' => 0]);

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE lessons MODIFY published TINYINT(1) NOT NULL DEFAULT 0");
        } elseif ($driver === 'pgsql') {
            DB::statement("ALTER TABLE lessons ALTER COLUMN published SET DEFAULT 0");
            DB::statement("ALTER TABLE lessons ALTER COLUMN published SET NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('lessons')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE lessons MODIFY published TINYINT(1) NULL DEFAULT 0");
        } elseif ($driver === 'pgsql') {
            DB::statement("ALTER TABLE lessons ALTER COLUMN published DROP NOT NULL");
            DB::statement("ALTER TABLE lessons ALTER COLUMN published DROP DEFAULT");
        }
    }
}
