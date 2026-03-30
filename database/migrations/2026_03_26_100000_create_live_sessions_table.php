<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLiveSessionsTable extends Migration
{
    public function up()
    {
        Schema::create('live_sessions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('course_id');
            $table->string('provider')->nullable();
            $table->date('session_date');
            $table->time('session_time');
            $table->text('meeting_link')->nullable();
            $table->string('meeting_id')->nullable();
            $table->text('host_url')->nullable();
            $table->integer('duration')->default(60);
            $table->enum('recurrence_type', ['daily', 'weekly', 'custom']);
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['course_id', 'session_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('live_sessions');
    }
}
