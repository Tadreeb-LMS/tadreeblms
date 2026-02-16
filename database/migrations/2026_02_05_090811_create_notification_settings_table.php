<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->string('module', 50)->index();
            $table->string('event', 100)->index();
            $table->string('channel', 30)->index();
            $table->boolean('is_enabled')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['module', 'event', 'channel']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notification_settings');
    }
}
