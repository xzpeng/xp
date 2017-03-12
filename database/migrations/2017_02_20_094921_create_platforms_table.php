<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlatformsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('platform_main', function (Blueprint $table) {
            $table->increments('id');
            $table->string('platform_name');
            $table->string('platform_ip');
            $table->string('platform_sn');
            $table->text('platform_system_info')->comment('主机状态');
            $table->integer('alive')->default(0)->comment('0:dead; 1:alive;');
            $table->dateTime('updated_status_time');
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
        Schema::drop('hosts');
    }
}
