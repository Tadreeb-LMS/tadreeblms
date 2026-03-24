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
        if (Schema::hasTable('lesson_videos')) {
            return;
        }

        Schema::create('lesson_videos', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('lesson_id');
            $table->foreign('lesson_id')->references('id')->on('lessons')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->string('type');
            $table->text('url')->nullable();
            $table->string('file_path')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_preview')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_videos');
    }
};
