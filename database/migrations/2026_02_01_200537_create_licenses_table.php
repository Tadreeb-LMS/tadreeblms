<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLicensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
public function up()
{
  Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->text('license_key'); // Encrypted
            $table->string('status')->default('pending'); // active, expired, revoked, invalid, pending
            $table->integer('max_users')->nullable();
            $table->string('license_type')->nullable(); // standard, enterprise, etc.
            $table->string('licensed_to')->nullable(); // Company/Organization name
            $table->string('licensee_email')->nullable();
            $table->timestamp('expiry_date')->nullable();
            $table->timestamp('support_valid_until')->nullable();
            $table->timestamp('last_validated_at')->nullable();
            $table->json('validation_response')->nullable(); // Cached full response from Keygen
            $table->json('metadata')->nullable(); // Additional license metadata
            $table->boolean('is_active')->default(true); // Only one active license at a time
            $table->timestamps();
        });

}


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('licenses');
    }
}
