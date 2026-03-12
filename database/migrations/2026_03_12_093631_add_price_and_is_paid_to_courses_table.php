<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPriceAndIsPaidToCoursesTable extends Migration
{
     public function up()
    {
        Schema::table('courses', function (Blueprint $table) {
            if (!Schema::hasColumn('courses', 'is_paid')) {
                $table->boolean('is_paid')->default(0)->after('is_online');
            }
        });
    }

    public function down()
    {
        Schema::table('courses', function (Blueprint $table) {
            if (Schema::hasColumn('courses', 'is_paid')) {
                $table->dropColumn('is_paid');
            }
        });
    }
}
