<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, clean up any existing duplicates so the unique index can be created.
        // For each set of duplicates keep the row with the lowest id and append
        // "-dupN" to the rest so no data is lost.
        $duplicates = DB::table('courses')
            ->select('course_code')
            ->whereNotNull('course_code')
            ->where('course_code', '!=', '')
            ->groupBy('course_code')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('course_code');

        foreach ($duplicates as $code) {
            $ids = DB::table('courses')
                ->where('course_code', $code)
                ->orderBy('id')
                ->pluck('id');

            // Skip the first (oldest) — it keeps the original code
            foreach ($ids->slice(1)->values() as $index => $id) {
                DB::table('courses')
                    ->where('id', $id)
                    ->update(['course_code' => $code . '-dup' . ($index + 1)]);
            }
        }

        Schema::table('courses', function (Blueprint $table) {
            $table->unique('course_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropUnique(['course_code']);
        });
    }
};
