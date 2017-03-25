<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableKexinPackageContentLists extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kexin_package_content_lists', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('package_id');
            $table->string('file_name');
            $table->string('file_hash');
            $table->integer('file_size');
            $table->integer('file_type');
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
