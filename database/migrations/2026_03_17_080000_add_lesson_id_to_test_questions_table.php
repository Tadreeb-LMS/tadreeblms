<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLessonIdToTestQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('test_questions')) {
            return;
        }

        Schema::table('test_questions', function (Blueprint $table) {
            if (!Schema::hasColumn('test_questions', 'lesson_id')) {
                $table->unsignedInteger('lesson_id')->nullable()->after('test_id');
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
        if (!Schema::hasTable('test_questions')) {
            return;
        }

        if (Schema::hasColumn('test_questions', 'lesson_id')) {
            Schema::table('test_questions', function (Blueprint $table) {
                $table->dropIndex(['lesson_id']);
                $table->dropColumn('lesson_id');
            });
        }
    }
}
