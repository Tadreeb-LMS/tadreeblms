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
        Schema::create('external_apps', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., 'Google Meet', 'Microsoft Teams', 'Zoom'
            $table->string('slug')->unique(); // e.g., 'google-meet', 'microsoft-teams', 'zoom'
            $table->text('description')->nullable();
            $table->boolean('is_enabled')->default(false);
            $table->string('version')->nullable();
            $table->string('installed_path')->nullable(); // Path where the module is installed
            $table->string('config_file')->nullable(); // Configuration file path
            $table->json('configuration')->nullable(); // Store configuration data as JSON
            $table->string('status')->default('inactive'); // active, inactive, error
            $table->text('error_message')->nullable(); // Store any error messages
            $table->timestamp('installed_at')->nullable();
            $table->timestamp('last_updated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('external_apps');
    }
};
