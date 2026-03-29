<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEnterpriseFeaturesToCertificatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->string('certificate_id')->unique()->nullable()->after('course_id');
            $table->string('validation_hash')->unique()->nullable()->after('certificate_id');
            $table->string('file_path')->nullable()->after('url');
            $table->timestamp('revoked_at')->nullable()->after('status');
            $table->json('metadata')->nullable()->after('revoked_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropColumn(['certificate_id', 'validation_hash', 'file_path', 'revoked_at', 'metadata']);
        });
    }
}
