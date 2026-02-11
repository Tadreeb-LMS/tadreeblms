<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReminderSentAtToSubscribeCourses extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('subscribe_courses', 'reminder_sent_at')) {
            Schema::table('subscribe_courses', function (Blueprint $table) {
                $table->timestamp('reminder_sent_at')->nullable()->after('completed_at');
            });
        }
    }

    public function down()
    {
        Schema::table('subscribe_courses', function (Blueprint $table) {
            $table->dropColumn('reminder_sent_at');
        });
    }
}
