<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableFilePolicies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('file_policies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('file_name');
            $table->string('file_hash');
            $table->integer('file_size');
            $table->string('file_dir');
            $table->integer('file_type');
            $table->string('file_op')->comment('write|read|rename|new|remove');
            $table->string('group_name');
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
