<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKpiSnapshotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('kpi_snapshots')) {
            return;
        }

        Schema::create('kpi_snapshots', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('kpi_id');
            $table->unsignedBigInteger('previous_snapshot_id')->nullable();
            $table->unsignedInteger('calculation_version')->default(1);
            $table->string('input_signature', 40);
            $table->boolean('excluded')->default(false);
            $table->decimal('value', 8, 2)->nullable();
            $table->decimal('weighted_score', 8, 2)->nullable();
            $table->decimal('total_active_weight', 10, 2)->default(0);
            $table->boolean('is_current')->default(true);
            $table->timestamp('calculated_at')->useCurrent();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['kpi_id', 'is_current'], 'kpi_snapshots_kpi_current_idx');
            $table->index(['kpi_id', 'calculated_at'], 'kpi_snapshots_kpi_calculated_idx');
            $table->index('input_signature', 'kpi_snapshots_signature_idx');

            $table->foreign('kpi_id')->references('id')->on('kpis')->onDelete('cascade');
            $table->foreign('previous_snapshot_id')->references('id')->on('kpi_snapshots')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kpi_snapshots');
    }
}
