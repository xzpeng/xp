<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableProcessPolicyConfigRecords extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('process_policy_config_records', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('process_id');
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
