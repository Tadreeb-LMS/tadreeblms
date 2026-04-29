<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKpiCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('kpi_category')) {
            return;
        }

        Schema::create('kpi_category', function (Blueprint $table) {
            $table->unsignedBigInteger('kpi_id');
            $table->unsignedInteger('category_id');
            $table->timestamps();

            $table->primary(['kpi_id', 'category_id']);
            $table->index('category_id');

            $table->foreign('kpi_id')->references('id')->on('kpis')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kpi_category');
    }
}
