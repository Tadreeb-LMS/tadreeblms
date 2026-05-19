<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFinalAssessmentMaxAttemptsToCoursesTable extends Migration
{
    public function up()
    {
        Schema::table('courses', function (Blueprint $table) {
            if (!Schema::hasColumn('courses', 'final_assessment_max_attempts')) {
                $table->unsignedSmallInteger('final_assessment_max_attempts')
                    ->nullable()
                    ->after('marks_required');
            }
        });
    }

    public function down()
    {
        Schema::table('courses', function (Blueprint $table) {
            if (Schema::hasColumn('courses', 'final_assessment_max_attempts')) {
                $table->dropColumn('final_assessment_max_attempts');
            }
        });
    }
}
