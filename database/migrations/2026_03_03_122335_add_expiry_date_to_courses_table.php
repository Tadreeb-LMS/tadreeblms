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
            if (!Schema::hasColumn('courses', 'start_date')) {
                // $table->date('start_date')->nullable()->after('published');
                  $table->date('start_date')->nullable()->after('status');
        // $table->date('expiry_date')->nullable()->after('start_date');
            }
            if (!Schema::hasColumn('courses', 'expiry_date')) {
                $table->date('expiry_date')->nullable()->after('start_date');
            }
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            //
        });
    }
};
