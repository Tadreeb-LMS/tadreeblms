<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLanguageMarketplacePackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('language_marketplace_packages', function (Blueprint $table) {
            $table->increments('id');
            $table->string('source_locale', 15)->default('en');
            $table->string('target_locale', 15);
            $table->string('package_type', 20)->default('translation');
            $table->string('status', 20)->default('draft');
            $table->string('title')->nullable();
            $table->string('version', 50)->nullable();
            $table->string('manifest_path')->nullable();
            $table->unsignedInteger('source_package_id')->nullable();
            $table->unsignedInteger('submitted_by')->nullable();
            $table->unsignedInteger('reviewed_by')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['target_locale', 'status']);
            $table->index(['package_type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('language_marketplace_packages');
    }
}
