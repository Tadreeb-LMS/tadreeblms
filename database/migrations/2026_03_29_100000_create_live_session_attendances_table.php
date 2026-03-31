<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLiveSessionAttendancesTable extends Migration
{
    public function up()
    {
        Schema::create('live_session_attendances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('live_session_id');
            $table->unsignedInteger('user_id');
            $table->dateTime('attended_at');
            $table->timestamps();

            $table->foreign('live_session_id')->references('id')->on('live_sessions')->onDelete('cascade');
            $table->unique(['live_session_id', 'user_id']);
            $table->index(['user_id', 'live_session_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('live_session_attendances');
    }
}
