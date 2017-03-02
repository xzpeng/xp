<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableHostFileTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('host_file', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('host_id')->comment('The id in hosts table');
            $table->integer('file_id')->comment('The id in softwares table');
            $table->string('upload_path');
            $table->integer('status')->comment('Software installing status: 1-申请,2-已安装');
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
        //
    }
}
