<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTablePlatformFileTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('platform_file', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('platform_id')->comment('The id in platforms table');
            $table->integer('file_id')->comment('The id in files table');
            $table->string('upload_path');
            $table->integer('status')->comment('File uploading status: 1-申请,2-已安装');
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
