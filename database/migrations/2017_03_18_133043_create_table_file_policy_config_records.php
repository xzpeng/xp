<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableFilePolicyConfigRecords extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('file_policy_config_records', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('file_id');
            $table->integer('platform_id');
            $table->string('platform_sn');
            $table->string('operate_result');
            $table->timestamp('operate_at');
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
