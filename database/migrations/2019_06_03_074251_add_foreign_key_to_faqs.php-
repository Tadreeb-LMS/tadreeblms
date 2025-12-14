<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddForeignKeyToFaqs extends Migration
{
    public function up()
    {
        // Ensure the column is unsigned (using raw SQL to avoid DBAL)
        DB::statement('ALTER TABLE faqs MODIFY category_id INT UNSIGNED');

        // Add the foreign key, ignore if it already exists
        $foreignKeys = DB::select("SELECT CONSTRAINT_NAME 
                                   FROM information_schema.KEY_COLUMN_USAGE 
                                   WHERE TABLE_NAME = 'faqs' 
                                     AND COLUMN_NAME = 'category_id' 
                                     AND CONSTRAINT_SCHEMA = DATABASE()");

        if (empty($foreignKeys)) {
            DB::statement('ALTER TABLE faqs ADD CONSTRAINT faqs_category_id_foreign FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE');
        }
    }

    public function down()
    {
        DB::statement('ALTER TABLE faqs DROP FOREIGN KEY IF EXISTS faqs_category_id_foreign');
    }
}
