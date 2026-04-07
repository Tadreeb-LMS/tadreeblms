<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWeightToKpisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('kpis') || Schema::hasColumn('kpis', 'weight')) {
            return;
        }

        Schema::table('kpis', function (Blueprint $table) {
            $table->decimal('weight', 8, 2)->default(1)->after('description');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('kpis') || !Schema::hasColumn('kpis', 'weight')) {
            return;
        }

        Schema::table('kpis', function (Blueprint $table) {
            $table->dropColumn('weight');
        });
    }
}