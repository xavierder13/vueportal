<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeOjtPerformanceRatingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_ojt_performance_ratings', function (Blueprint $table) {
            $table->id();
            $table->integer('employee_id');
            $table->string('mentor');
            $table->decimal('grade', 8, 2);
            $table->decimal('kpi', 8, 2);
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
        Schema::dropIfExists('employee_ojt_performance_ratings');
    }
}
