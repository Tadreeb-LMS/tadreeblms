<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCourseAndLessonColumnsToQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('questions')) {
            return;
        }

        Schema::table('questions', function (Blueprint $table) {
            if (!Schema::hasColumn('questions', 'course_id')) {
                $table->unsignedInteger('course_id')->nullable()->after('user_id');
                $table->index('course_id');
            }

            if (!Schema::hasColumn('questions', 'lesson_id')) {
                $table->unsignedInteger('lesson_id')->nullable()->after('course_id');
                $table->index('lesson_id');
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
        if (!Schema::hasTable('questions')) {
            return;
        }

        if (Schema::hasColumn('questions', 'lesson_id')) {
            Schema::table('questions', function (Blueprint $table) {
                $table->dropIndex(['lesson_id']);
                $table->dropColumn('lesson_id');
            });
        }

        if (Schema::hasColumn('questions', 'course_id')) {
            Schema::table('questions', function (Blueprint $table) {
                $table->dropIndex(['course_id']);
                $table->dropColumn('course_id');
            });
        }
    }
}
