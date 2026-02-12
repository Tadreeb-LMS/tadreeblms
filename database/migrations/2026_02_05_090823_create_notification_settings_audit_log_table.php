<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationSettingsAuditLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification_settings_audit_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('notification_setting_id')->nullable();
            $table->unsignedInteger('user_id');
            $table->string('action', 30);
            $table->string('module', 50)->nullable();
            $table->string('event', 100)->nullable();
            $table->string('channel', 30)->nullable();
            $table->boolean('old_value')->nullable();
            $table->boolean('new_value')->nullable();
            $table->json('changes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->foreign('notification_setting_id')
                ->references('id')
                ->on('notification_settings')
                ->onDelete('set null');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->index(['module', 'event', 'channel']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notification_settings_audit_log');
    }
}
