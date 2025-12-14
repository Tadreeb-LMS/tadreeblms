<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeyToFaqs extends Migration
{
    public function up()
    {
        Schema::table('faqs', function (Blueprint $table) {
            // Ensure column exists and matches categories.id type
            if (!Schema::hasColumn('faqs', 'category_id')) {
                $table->unsignedInteger('category_id');
            } else {
                // Drop existing column and recreate it if type mismatch
                $table->dropColumn('category_id');
                $table->unsignedInteger('category_id');
            }

            // Add foreign key
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
            $table->dropColumn('category_id');
        });
    }
}
