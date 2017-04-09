<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableAccessPolicies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('access_policies', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('platform_id');
            $table->string('sub_name');
            $table->string('sub_hash');
            $table->string('folder_name');
            $table->string('folder_hash');
            $table->integer('folder_type');
            $table->string('folder_op')->comment('write|read|rename|new|remove');
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
