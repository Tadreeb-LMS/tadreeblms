<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourseModuleWeightagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('course_module_weightages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->cascadeOnDelete();
            $table->integer('minimun_qualify_marks');
            $table->string('module_included');
            $table->string('weightage');
            $table->string('last_module');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('course_module_weightages');
    }
}
