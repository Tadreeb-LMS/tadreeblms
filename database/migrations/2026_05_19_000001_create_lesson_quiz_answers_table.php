<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLessonQuizAnswersTable extends Migration
{
    public function up()
    {
        Schema::create('lesson_quiz_answers', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tests_result_id');
            $table->unsignedBigInteger('question_id');
            $table->unsignedInteger('user_id')->nullable();
            $table->longText('answer_text')->nullable();
            $table->longText('option_ids')->nullable();
            $table->tinyInteger('is_correct')->nullable();
            $table->unsignedInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['tests_result_id', 'question_id']);
            $table->index(['question_id', 'is_correct']);
            $table->index(['user_id', 'is_correct']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('lesson_quiz_answers');
    }
}
