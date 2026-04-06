<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKpiStatusHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('kpi_status_histories')) {
            return;
        }

        Schema::create('kpi_status_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('kpi_id');
            $table->string('action', 50);
            $table->boolean('previous_is_active')->nullable();
            $table->boolean('new_is_active')->nullable();
            $table->unsignedInteger('changed_by')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('kpi_id')->references('id')->on('kpis')->onDelete('cascade');
            $table->foreign('changed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kpi_status_histories');
    }
}