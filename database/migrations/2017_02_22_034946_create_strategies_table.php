<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStrategiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('strategies', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('host_id')->comment('The id in hosts table');
            $table->string('module');
            $table->string('func');
            $table->string('info_username')->nullable();
            $table->string('info_passwd')->nullable();
            $table->string('info_role')->nullable();
            $table->string('info_process_name')->nullable();
            $table->string('info_process_size')->nullable();
            $table->string('info_process_hash')->nullable();
            $table->string('info_file_name')->nullable();
            $table->string('info_file_size')->nullable();
            $table->string('info_file_hash')->nullable();
            $table->string('info_file_opt')->nullable();
            $table->string('info_active_starttime')->nullable();
            $table->string('info_active_endtime')->nullable();
            $table->string('info_platform_name');
            $table->string('info_platform_sn');
            $table->string('info_platform_ip');
            
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
        Schema::drop('strategies');
    }
}
