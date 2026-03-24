<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSoftDeleteColumnsToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {

            // Soft delete tracking
            $table->boolean('is_deleted')->default(0)->after('email');

            // Optional account status
            $table->enum('account_status', [
                'active',
                'suspended',
                'terminated'
            ])->default('active')->after('is_deleted');

        });

        // Modify unique constraint for email reuse
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_email_unique');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unique(['email','is_deleted']);
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_email_is_deleted_unique');
            $table->dropColumn('is_deleted');
            $table->dropColumn('account_status');
            $table->unique('email');
        });
    }
}
