<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableSecuritysoftPackages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('securitysoft_packages', function (Blueprint $table) {
            $table->increments('id');
            $table->string('soft_name');
            $table->string('soft_hash');
            $table->string('soft_size');
            $table->string('soft_dir');
            $table->string('soft_release');
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
