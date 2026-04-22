<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKpiCourseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('kpi_course')) {
            return;
        }

        Schema::create('kpi_course', function (Blueprint $table) {
            $table->unsignedBigInteger('kpi_id');
            $table->unsignedInteger('course_id');
            $table->timestamps();

            $table->primary(['kpi_id', 'course_id']);
            $table->index('course_id');

            $table->foreign('kpi_id')->references('id')->on('kpis')->onDelete('cascade');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kpi_course');
    }
}
