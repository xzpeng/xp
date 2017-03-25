<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableKexinPackages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kexin_packages', function (Blueprint $table) {
            $table->increments('id');
            $table->string('package_name');
            $table->string('package_hash');
            $table->integer('package_size');
            $table->string('package_dir');
            $table->integer('package_release');
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
