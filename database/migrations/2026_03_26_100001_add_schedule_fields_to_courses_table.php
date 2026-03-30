<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddScheduleFieldsToCoursesTable extends Migration
{
    public function up()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->enum('schedule_type', ['single', 'daily', 'weekly', 'custom'])->default('single')->after('meeting_timezone');
            $table->json('schedule_days')->nullable()->after('schedule_type');
            $table->date('last_session_date')->nullable()->after('schedule_days');
        });
    }

    public function down()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['schedule_type', 'schedule_days', 'last_session_date']);
        });
    }
}
