<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('live_sessions')) {
            return;
        }

        Schema::table('live_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('live_sessions', 'meeting_link')) {
                $table->text('meeting_link')->nullable()->after('session_time');
            }

            if (!Schema::hasColumn('live_sessions', 'meeting_provider')) {
                $table->enum('meeting_provider', ['zoom', 'teams', 'google_meet'])->nullable()->after('meeting_link');
            }
        });

        if (Schema::hasColumn('live_sessions', 'provider')) {
            DB::table('live_sessions')
                ->whereNull('meeting_provider')
                ->whereNotNull('provider')
                ->orderBy('id')
                ->chunkById(100, function ($sessions) {
                    foreach ($sessions as $session) {
                        $provider = match ($session->provider) {
                            'zoom' => 'zoom',
                            'teams' => 'teams',
                            'google_meet', 'google-meet-integration' => 'google_meet',
                            default => null,
                        };

                        if ($provider) {
                            DB::table('live_sessions')
                                ->where('id', $session->id)
                                ->update(['meeting_provider' => $provider]);
                        }
                    }
                });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('live_sessions') || !Schema::hasColumn('live_sessions', 'meeting_provider')) {
            return;
        }

        Schema::table('live_sessions', function (Blueprint $table) {
            $table->dropColumn('meeting_provider');
        });
    }
};
