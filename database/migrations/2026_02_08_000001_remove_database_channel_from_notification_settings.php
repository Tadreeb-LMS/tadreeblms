<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class RemoveDatabaseChannelFromNotificationSettings extends Migration
{
    public function up()
    {
        DB::table('notification_settings')->where('channel', 'database')->delete();
    }

    public function down()
    {
        // Cannot restore deleted rows — no-op
    }
}
