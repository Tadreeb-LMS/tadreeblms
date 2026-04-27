<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKpiExportNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kpi_export_notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->string('status', 20)->default('pending');
            $table->unsignedTinyInteger('progress')->default(0);
            $table->string('format', 10)->default('csv');
            $table->json('filters')->nullable();
            $table->text('file_path')->nullable();
            $table->text('download_link')->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedInteger('processed_rows')->default(0);
            $table->unsignedInteger('total_rows')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kpi_export_notifications');
    }
}
