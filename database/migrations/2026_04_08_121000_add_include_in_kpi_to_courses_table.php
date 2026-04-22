<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIncludeInKpiToCoursesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('courses') || Schema::hasColumn('courses', 'include_in_kpi')) {
            return;
        }

        Schema::table('courses', function (Blueprint $table) {
            $table->boolean('include_in_kpi')->default(true)->after('category_id');
            $table->index('include_in_kpi');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('courses') || !Schema::hasColumn('courses', 'include_in_kpi')) {
            return;
        }

        Schema::table('courses', function (Blueprint $table) {
            $table->dropIndex(['include_in_kpi']);
            $table->dropColumn('include_in_kpi');
        });
    }
}
