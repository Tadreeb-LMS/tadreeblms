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
        if (!Schema::hasTable('email_campain')) {
            Schema::create('email_campain', function (Blueprint $table) {
                $table->id();
                $table->string('campain_subject', 500);
                $table->longText('content')->nullable();
                $table->text('link')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('email_campain_users')) {
            Schema::create('email_campain_users', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('campain_id');
                $table->string('email');
                $table->string('status', 50)->default('in-queue');
                $table->timestamp('sent_at')->nullable();
                $table->timestamps();

                $table->index('campain_id');
                $table->index('email');
                $table->unique(['campain_id', 'email']);
                $table->foreign('campain_id')->references('id')->on('email_campain')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_campain_users');
        Schema::dropIfExists('email_campain');
    }
};
