<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableSecuritysoftSendRecords extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('securitysoft_send_records', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('soft_id');
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
