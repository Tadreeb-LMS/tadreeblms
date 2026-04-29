<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKpiUserCursorsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('kpi_user_cursors')) {
            return;
        }

        Schema::create('kpi_user_cursors', function (Blueprint $table) {
            $table->bigIncrements('id');

            // The user whose KPI progress this cursor tracks.
            $table->unsignedInteger('user_id');

            // The KPI definition this cursor belongs to.
            $table->unsignedBigInteger('kpi_id');

            // The last lms_kpi_events.id that has been fully incorporated into
            // computed_value. NULL means no events have been processed yet.
            $table->unsignedBigInteger('last_event_id')->nullable()->default(null);

            // Pre-computed KPI percentage (0.00 – 100.00) for this user/KPI pair.
            // NULL means the value has never been computed.
            $table->decimal('computed_value', 8, 4)->nullable()->default(null);

            // Number of events that contributed to computed_value.
            // Used as the denominator for accurate running-average calculations.
            $table->unsignedInteger('event_count')->default(0);

            // Arbitrary type-specific state needed for accurate incremental updates
            // (e.g. running sums that cannot be reconstructed from computed_value alone).
            $table->json('checkpoint_data')->nullable()->default(null);

            // When true the cursor is considered corrupt or stale and the next
            // call to processIncrementalForUser() will fall back to a full
            // recalculation before clearing this flag.
            $table->boolean('is_dirty')->default(false);

            // Timestamp of the last successful processing run.
            $table->timestamp('last_processed_at')->nullable()->default(null);

            $table->timestamps();

            // One cursor per (user, KPI).
            $table->unique(['user_id', 'kpi_id'], 'kpi_user_cursors_user_kpi_unique');

            $table->index('kpi_id', 'kpi_user_cursors_kpi_id_idx');
            $table->index('is_dirty', 'kpi_user_cursors_is_dirty_idx');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('kpi_id')
                ->references('id')
                ->on('kpis')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('kpi_user_cursors');
    }
}
