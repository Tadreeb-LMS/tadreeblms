<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLanguageTranslationInvitationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('language_translation_invitations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('source_package_id')->nullable();
            $table->unsignedInteger('submission_package_id')->nullable();
            $table->string('locale_code', 15);
            $table->string('contributor_name')->nullable();
            $table->string('contributor_email');
            $table->string('invite_token', 100)->unique();
            $table->string('status', 20)->default('pending');
            $table->unsignedInteger('invited_by')->nullable();
            $table->unsignedInteger('reviewed_by')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('viewed_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['locale_code', 'status']);
            $table->index(['contributor_email', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('language_translation_invitations');
    }
}
