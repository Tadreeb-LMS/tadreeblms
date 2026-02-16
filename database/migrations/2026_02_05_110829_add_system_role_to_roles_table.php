<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->boolean('system_role')
                  ->default(0)
                  ->after('name'); // change position if needed
        });

        // Mark core system roles
        DB::table('roles')
            ->whereIn('name', ['administrator', 'teacher', 'student'])
            ->update(['system_role' => 1]);
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('system_role');
        });
    }
};
