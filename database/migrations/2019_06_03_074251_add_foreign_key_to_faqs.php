<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeyToFaqs extends Migration
{
    public function up()
    {
        Schema::table('faqs', function (Blueprint $table) {
            // Ensure the column exists and is unsigned integer
            if (!Schema::hasColumn('faqs', 'category_id')) {
                $table->unsignedInteger('category_id');
            }
            // Add the foreign key
            $table->foreign('category_id')
                  ->references('id')
                  ->on('categories')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('faqs', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
        });
    }
}
