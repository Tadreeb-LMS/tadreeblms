<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLanguageLibraryFieldsToLocalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('locales')) {
            return;
        }

        Schema::table('locales', function (Blueprint $table) {
            if (!Schema::hasColumn('locales', 'is_enabled')) {
                $table->tinyInteger('is_enabled')->default(1)->after('is_default');
            }

            if (!Schema::hasColumn('locales', 'library_package_path')) {
                $table->string('library_package_path')->nullable()->after('is_enabled');
            }

            if (!Schema::hasColumn('locales', 'library_uploaded_at')) {
                $table->timestamp('library_uploaded_at')->nullable()->after('library_package_path');
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
        if (!Schema::hasTable('locales')) {
            return;
        }

        Schema::table('locales', function (Blueprint $table) {
            if (Schema::hasColumn('locales', 'library_uploaded_at')) {
                $table->dropColumn('library_uploaded_at');
            }

            if (Schema::hasColumn('locales', 'library_package_path')) {
                $table->dropColumn('library_package_path');
            }

            if (Schema::hasColumn('locales', 'is_enabled')) {
                $table->dropColumn('is_enabled');
            }
        });
    }
}
