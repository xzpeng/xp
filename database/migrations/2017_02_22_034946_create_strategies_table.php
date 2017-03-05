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

            $table->integer('platform_id')->comment('The id in platforms table');
            $table->integer('author')->comment('Who added this strategy');
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
            $table->string('info_remote_name')->comment('远程计算机名');
            $table->string('info_remote_sn')->comment('远程计算机序列号');
            $table->string('info_remote_ip')->comment('远程计算机IP');
            $table->string('info_remote_un')->comment('远程计算机用户名');
            $table->string('info_remote_up')->comment('远程计算机密码');
            $table->string('info_soft_path')->comment('软件路径');
            $table->smallInteger('is_deleted')->default(0);
            
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
