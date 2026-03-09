<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->string('meeting_provider')->nullable();
            $table->string('meeting_id')->nullable();
            $table->text('meeting_join_url')->nullable();
            $table->text('meeting_host_url')->nullable();
            $table->dateTime('meeting_start_at')->nullable();
            $table->integer('meeting_duration')->nullable();
            $table->string('meeting_timezone')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn([
                'meeting_provider',
                'meeting_id',
                'meeting_join_url',
                'meeting_host_url',
                'meeting_start_at',
                'meeting_duration',
                'meeting_timezone',
            ]);
        });
    }
};
