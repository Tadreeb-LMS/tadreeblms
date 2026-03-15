<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Create1500441827CoursesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(! Schema::hasTable('courses')) {
            Schema::create('courses', function (Blueprint $table) {
                $table->increments('id');
            $table->text('temp_id')->nullable();
            $table->unsignedInteger('category_id')->nullable();

            $table->string('title', 191);
            $table->string('slug', 191)->nullable();

            $table->text('description')->nullable();
            $table->string('course_link', 255)->nullable();

            $table->decimal('price', 15, 2)->nullable();
            $table->decimal('strike', 15, 2)->nullable();

            $table->string('course_image', 191)->nullable();
            $table->integer('department_id')->nullable();

            $table->date('start_date')->nullable();

            $table->integer('featured')->default(0);
            $table->integer('trending')->default(0);
            $table->integer('popular')->default(0);

            $table->text('meta_title')->nullable();
            $table->longText('meta_description')->nullable();
            $table->longText('meta_keywords')->nullable();

            $table->tinyInteger('published')->default(0);
            $table->tinyInteger('free')->default(0);
            $table->tinyInteger('cms')->default(0);

            $table->date('expire_at')->nullable();

            $table->integer('course_type')->default(0)
                  ->comment('2 internal, 3 external');

            $table->integer('marks_required')->nullable();

            $table->string('course_code', 191)->nullable();
            $table->string('arabic_title', 191)->nullable();
    
            $table->string('course_lang', 30)->default('english');
            $table->string('is_online', 15)->default('Online');

            $table->timestamps();
            $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('courses');
    }
}
