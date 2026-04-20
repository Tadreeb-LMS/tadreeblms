<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGithubSyncFieldsToLanguageMarketplacePackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('language_marketplace_packages')) {
            return;
        }

        Schema::table('language_marketplace_packages', function (Blueprint $table) {
            if (!Schema::hasColumn('language_marketplace_packages', 'github_sync_status')) {
                $table->string('github_sync_status', 20)->nullable()->after('published_at');
            }
            if (!Schema::hasColumn('language_marketplace_packages', 'github_sync_sha')) {
                $table->string('github_sync_sha')->nullable()->after('github_sync_status');
            }
            if (!Schema::hasColumn('language_marketplace_packages', 'github_sync_url')) {
                $table->string('github_sync_url')->nullable()->after('github_sync_sha');
            }
            if (!Schema::hasColumn('language_marketplace_packages', 'github_sync_error')) {
                $table->text('github_sync_error')->nullable()->after('github_sync_url');
            }
            if (!Schema::hasColumn('language_marketplace_packages', 'github_synced_at')) {
                $table->timestamp('github_synced_at')->nullable()->after('github_sync_error');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('language_marketplace_packages')) {
            return;
        }

        Schema::table('language_marketplace_packages', function (Blueprint $table) {
            if (Schema::hasColumn('language_marketplace_packages', 'github_synced_at')) {
                $table->dropColumn('github_synced_at');
            }
            if (Schema::hasColumn('language_marketplace_packages', 'github_sync_error')) {
                $table->dropColumn('github_sync_error');
            }
            if (Schema::hasColumn('language_marketplace_packages', 'github_sync_url')) {
                $table->dropColumn('github_sync_url');
            }
            if (Schema::hasColumn('language_marketplace_packages', 'github_sync_sha')) {
                $table->dropColumn('github_sync_sha');
            }
            if (Schema::hasColumn('language_marketplace_packages', 'github_sync_status')) {
                $table->dropColumn('github_sync_status');
            }
        });
    }
}
