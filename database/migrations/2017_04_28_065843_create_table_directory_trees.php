<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableDirectoryTrees extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('directory_trees', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('parent_id');
            $table->integer('platform_id');
            $table->integer('order');
            $table->string('name');
            $table->string('name_relative');
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
