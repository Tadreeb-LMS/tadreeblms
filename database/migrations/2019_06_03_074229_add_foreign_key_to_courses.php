<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeyToCourses extends Migration
{
    public function up()
    {
        Schema::table('courses', function (Blueprint $table) {
            // Only add the foreign key without changing the column type
            // Make sure the column is already integer and unsigned
            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
        });
    }
}
