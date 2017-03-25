<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlatformMainsTable extends Migration
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
            $table->string('platform_root');
            $table->string('platform_rootpwd');
            $table->integer('securitysoft_id')->comment('The id in securitysoft_packages table');
            $table->integer('install_status')->comment('Software installing status: 1-申请,2-已安装');
            $table->text('platform_system_info')->comment('主机状态');
            $table->integer('alive')->default(0)->comment('0:dead; 1:alive;');
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
