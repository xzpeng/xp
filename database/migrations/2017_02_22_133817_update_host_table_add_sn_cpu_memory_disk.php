<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateHostTableAddSnCpuMemoryDisk extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('hosts', function (Blueprint $table) {
            $table->string('sn')->after('ip')->comment('平台序列号 platform_sn');
            $table->text('cpu')->after('sn')->comment('CPU使用情况');
            $table->string('memory')->after('cpu')->comment('内存使用情况');
            $table->text('disk')->after('memory')->comment('存储使用情况');
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


