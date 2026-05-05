<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKpiRoleConfigsTable extends Migration
{
    public function up()
    {
        Schema::create('kpi_role_configs', function (Blueprint $table) {
            $table->bigIncrements('id');

            // References Spatie roles table
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('kpi_id');

            // null means "use the KPI's global default"
            $table->float('weight_override')->nullable();
            $table->boolean('is_active_override')->nullable();

            $table->timestamps();

            $table->unique(['role_id', 'kpi_id']);

            $table->foreign('kpi_id')
                ->references('id')
                ->on('kpis')
                ->onDelete('cascade');

            // Spatie roles table name is configurable; use generic foreign key without constraint
            // so it works regardless of the configured guard / table name.
            $table->index('role_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('kpi_role_configs');
    }
}
