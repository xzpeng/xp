<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableKexinPackageTransferRecords extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kexin_package_transfer_records', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('package_id');
            $table->integer('platform_id');
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
