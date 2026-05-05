<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKpiTargetsTable extends Migration
{
    public function up()
    {
        Schema::create('kpi_targets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('kpi_id');
            $table->unsignedInteger('role_id')->nullable();
            $table->unsignedInteger('course_id')->nullable();
            $table->float('target_value');
            $table->timestamps();

            $table->index('kpi_id');
            $table->index('role_id');
            $table->index('course_id');
            $table->index(['kpi_id', 'role_id', 'course_id']);

            $table->foreign('kpi_id')
                ->references('id')
                ->on('kpis')
                ->onDelete('cascade');

            $table->foreign('course_id')
                ->references('id')
                ->on('courses')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('kpi_targets');
    }
}
