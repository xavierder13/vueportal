<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTacticalRequisitionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tactical_requisitions', function (Blueprint $table) {
            $table->id();
            $table->integer('branch_id');
            $table->integer('user_id');
            $table->integer('marketing_event_id');
            $table->string('venue');
            $table->string('sponsor');
            $table->date('period_from');
            $table->date('period_to');
            $table->string('operating_from');
            $table->string('operating_to');
            $table->string('status');
            $table->date('prev_date')->nullable();
            $table->string('prev_venue');
            $table->string('prev_sponsor');
            $table->date('date_approve')->nullable();
            $table->date('date_cancelled')->nullable();
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
        Schema::dropIfExists('tactical_requisitions');
    }
}
