<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFileUploadLogIdToEmployeeAttlogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employee_attlogs', function (Blueprint $table) {
            $table->integer('file_upload_log_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employee_attlogs', function (Blueprint $table) {
            $table->dropColumn('file_upload_log_id');
        });
    }
}
