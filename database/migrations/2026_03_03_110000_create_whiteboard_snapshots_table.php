<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWhiteboardSnapshotsTable extends Migration
{
    public function up()
    {
        Schema::create('whiteboard_snapshots', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('course_id')->nullable(); // null for test sessions
            $table->string('image_path'); // relative path in storage
            $table->string('file_name');
            $table->unsignedBigInteger('file_size')->default(0); // bytes
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'course_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('whiteboard_snapshots');
    }
}
