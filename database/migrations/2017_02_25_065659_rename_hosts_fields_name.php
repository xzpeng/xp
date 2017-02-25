<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameHostsFieldsName extends Migration
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
            $table->renameColumn('name', 'host_name');
            $table->renameColumn('ip', 'host_ip');
            $table->renameColumn('sn', 'host_sn');
            $table->dropColumn('cpu', 'memory', 'disk');
            $table->text('host_stat')->comment('主机状态');
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
