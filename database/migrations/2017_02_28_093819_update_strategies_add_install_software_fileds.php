<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateStrategiesAddInstallSoftwareFileds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('strategies', function (Blueprint $table) {
            $table->string('info_remote_name')->after('info_platform_ip')->comment('远程计算机名');
            $table->string('info_remote_sn')->after('info_remote_name')->comment('远程计算机序列号');
            $table->string('info_remote_ip')->after('info_remote_sn')->comment('远程计算机IP');
            $table->string('info_remote_un')->after('info_remote_ip')->comment('远程计算机用户名');
            $table->string('info_remote_up')->after('info_remote_un')->comment('远程计算机密码');
            $table->string('info_soft_path')->after('info_remote_up')->comment('软件路径');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
