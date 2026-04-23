<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKpiTemplatesTable extends Migration
{
    public function up()
    {
        Schema::create('kpi_templates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('category')->default('general'); // corporate_training, compliance, sales, customer_success, employee_development
            $table->text('use_case')->nullable(); // Detailed explanation of when to use
            $table->unsignedInteger('item_count')->default(0); // Cached count
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('kpi_template_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('template_id');
            $table->string('name');
            $table->string('code');
            $table->text('description')->nullable();
            $table->string('type'); // percentage, numeric, ratio, etc.
            $table->decimal('weight', 5, 2)->default(1.00);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('template_id')
                ->references('id')
                ->on('kpi_templates')
                ->onDelete('cascade');

            $table->unique(['template_id', 'code']);
            $table->index('template_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('kpi_template_items');
        Schema::dropIfExists('kpi_templates');
    }
}
