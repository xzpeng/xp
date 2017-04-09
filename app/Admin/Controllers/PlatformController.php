<?php

namespace App\Admin\Controllers;

use App\Models\Platform;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\Tab;
use Encore\Admin\Widgets\Table;

class PlatformController extends Controller
{
    use ModelForm;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('header');
            $content->description('description');

            $content->body($this->grid());
        });
    }


    public function show($id)
    {
        return Admin::content(function (Content $content) use($id) {
            $platform = Platform::find($id);

            $content->header('设备状态监控');
            // $content->description('主机信息查看、管理……');

            $tab = new Tab();

            if($platform_system_info = json_decode($platform->platform_system_info, true)) {
                $boottime = $platform_system_info['boottime'];
                $networks = $platform_system_info['network'];
                $cpu_stat = isset($platform_system_info['cpu'])?$platform_system_info['cpu']:0;
                $memory_stat = ( isset($platform_system_info['memory'])&&isset($platform_system_info['memory']['available'])&&isset($platform_system_info['memory']['total']) )?round(($platform_system_info['memory']['available']*100/$platform_system_info['memory']['total']), 1):0;

                $disk_html = '';
                foreach($platform_system_info['disk'] as $key => $value) {
                    $disk_html .= '<div class="col-sm-8">'.$key.'
                                <div class="progress">
                                    <div class="progress-bar progress-bar-primary" role="progressbar" aria-valuenow="" aria-valuemin="0" aria-valuemax="100" style="width: ' . $value['usage'] . '%">' . $value['usage'] . '
                                      <span class="sr-only"></span>
                                    </div>
                                </div>
                            </div>';
                }

                $system_release = isset($platform_system_info['system_release'])?$platform_system_info['system_release']:'';
            } else {
                $cpu_stat = 0;
                $memory_stat = 0;
                $system_release = '';
            }
            $cpu_stat_class = $cpu_stat<50?'progress-bar-success':($cpu_stat>75?'progress-bar-danger':'progress-bar-warning');
            $memory_stat_class = $memory_stat<50?'progress-bar-success':($memory_stat>75?'progress-bar-danger':'progress-bar-warning');

            $platform_alive = $platform->alive?'<span class="label label-info">Alive</span>':'<span class="label label-default">Dead</span>';


            $info_html = <<<HTML
<div class="col-xs-offset-2 col-xs-8">
<table class="table table-striped">
    <tr>
        <td>主机名：</td>
        <td>$platform->platform_name</td>
    </tr>
    <tr>
        <td>IP地址：</td>
        <td>$platform->platform_ip</td>
    </tr>
    <tr>
        <td>序列号：</td>
        <td>$platform->platform_sn</td>
    </tr>
    <tr>
        <td>CPU：</td>
        <td title="$cpu_stat%">
            <div class="col-sm-12">
                <div class="progress">
                    <div class="progress-bar $cpu_stat_class progress-bar-striped active" role="progressbar" aria-valuenow="" aria-valuemin="0" aria-valuemax="100" style="width: $cpu_stat%">
                        $cpu_stat%
                    </div>
                </div>
            </div>
        </td>
    </tr>
    <tr>
        <td>内存：</td>
        <td title="$memory_stat%">
            <div class="col-sm-12">
                <div class="progress">
                    <div class="progress-bar $memory_stat_class progress-bar-striped active" role="progressbar" aria-valuenow="" aria-valuemin="0" aria-valuemax="100" style="width: $memory_stat%">
                      $memory_stat%
                    </div>
                </div>
            </div>
        </td>
    </tr>
    <tr>
        <td>存储：</td>
        <td>
            $disk_html
        </td>
    </tr>
    <tr>
        <td>系统版本：</td>
        <td>$system_release</td>
    </tr>
    <tr>
        <td>开机时间：</td>
        <td>$boottime</td>
    </tr>
    <tr>
        <td>状态：</td>
        <td>
        $platform_alive
    </tr>
</table>
</div>
HTML;
            $info_box = new Box('主机信息', $info_html);
            $tab->add('主机信息', $info_box, 'base_info');

            // 获取进程信息
            $xml_data = array(
                            'module' => 'system_info',
                            'func' => 'get_process',
                            'info' => array()
                        );

            $socketClient = new \App\SocketClient($platform->platform_ip, config('app.socket_remote_port'), $xml_data);
            $socket_response = $socketClient->send();
            $socketClient->close();

           /* $xml = '<?xml version="1.0" encoding="UTF-8"?><Response><result>Success</result><message><item><pid>1</pid><exe>/sbin/init</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:36.030000</create_time></item><item><pid>2</pid><exe>kernel thread</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:36.030000</create_time></item><item><pid>3</pid><exe>kernel thread</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:36.050000</create_time></item><item><pid>4</pid><exe>kernel thread</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:36.050000</create_time></item><item><pid>5</pid><exe>kernel thread</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:36.050000</create_time></item><item><pid>6</pid><exe>kernel thread</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:36.050000</create_time></item><item><pid>7</pid><exe>kernel thread</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:36.050000</create_time></item><item><pid>8</pid><exe>kernel thread</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:36.050000</create_time></item><item><pid>9</pid><exe>kernel thread</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:36.050000</create_time></item><item><pid>10</pid><exe>kernel thread</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:36.050000</create_time></item><item><pid>11</pid><exe>kernel thread</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:36.050000</create_time></item><item><pid>12</pid><exe>kernel thread</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:36.050000</create_time></item><item><pid>13</pid><exe>kernel thread</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:36.070000</create_time></item><item><pid>14</pid><exe>kernel thread</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:36.070000</create_time></item><item><pid>15</pid><exe>kernel thread</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:36.070000</create_time></item><item><pid>16</pid><exe>kernel thread</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:36.070000</create_time></item><item><pid>17</pid><exe>kernel thread</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:36.070000</create_time></item><item><pid>18</pid><exe>kernel thread</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:36.070000</create_time></item><item><pid>19</pid><exe>kernel thread</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:36.070000</create_time></item><item><pid>20</pid><exe>kernel thread</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:36.260000</create_time></item><item><pid>21</pid><exe>kernel thread</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:36.260000</create_time></item><item><pid>22</pid><exe>kernel thread</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:36.260000</create_time></item><item><pid>23</pid><exe>kernel thread</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:36.260000</create_time></item><item><pid>24</pid><exe>kernel thread</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:36.260000</create_time></item><item><pid>25</pid><exe>kernel thread</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:36.260000</create_time></item><item><pid>26</pid><exe>kernel thread</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:36.260000</create_time></item><item><pid>27</pid><exe>kernel thread</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:36.260000</create_time></item><item><pid>28</pid><exe>kernel thread</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:36.820000</create_time></item><item><pid>29</pid><exe>kernel thread</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:36.820000</create_time></item><item><pid>30</pid><exe>kernel thread</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:36.820000</create_time></item><item><pid>31</pid><exe>kernel thread</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:36.820000</create_time></item><item><pid>32</pid><exe>kernel thread</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:36.820000</create_time></item><item><pid>37</pid><exe>kernel thread</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:36.830000</create_time></item><item><pid>38</pid><exe>kernel thread</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:36.860000</create_time></item><item><pid>40</pid><exe>kernel thread</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:37.200000</create_time></item><item><pid>41</pid><exe>kernel thread</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:37.200000</create_time></item><item><pid>71</pid><exe>kernel thread</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:37.270000</create_time></item><item><pid>144</pid><exe>kernel thread</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:37.390000</create_time></item><item><pid>145</pid><exe>kernel thread</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:37.400000</create_time></item><item><pid>148</pid><exe>kernel thread</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:37.620000</create_time></item><item><pid>149</pid><exe>kernel thread</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:37.620000</create_time></item><item><pid>150</pid><exe>kernel thread</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:37.690000</create_time></item><item><pid>270</pid><exe>kernel thread</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:38.410000</create_time></item><item><pid>271</pid><exe>kernel thread</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:38.410000</create_time></item><item><pid>367</pid><exe>kernel thread</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:41.070000</create_time></item><item><pid>368</pid><exe>/sbin/udevd</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:41.250000</create_time></item><item><pid>682</pid><exe>kernel thread</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:43.620000</create_time></item><item><pid>769</pid><exe>kernel thread</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:43.900000</create_time></item><item><pid>899</pid><exe>kernel thread</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:44.870000</create_time></item><item><pid>900</pid><exe>kernel thread</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:44.870000</create_time></item><item><pid>949</pid><exe>kernel thread</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:45.610000</create_time></item><item><pid>1251</pid><exe>/usr/lib/vmware-tools/bin64/appLoader</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:47.140000</create_time></item><item><pid>1272</pid><exe>/usr/lib/vmware-tools/sbin64/vmtoolsd</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:47.260000</create_time></item><item><pid>1297</pid><exe>/usr/lib/vmware-tools/bin64/appLoader</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:47.510000</create_time></item><item><pid>1350</pid><exe>/usr/lib/vmware-caf/pme/bin/ManagementAgentHost</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:48.010000</create_time></item><item><pid>1553</pid><exe>/sbin/auditd</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:52.220000</create_time></item><item><pid>1578</pid><exe>/sbin/rsyslogd</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:52.480000</create_time></item><item><pid>1648</pid><exe>/bin/dbus-daemon</exe><status>sleeping</status><user>dbus</user><create_time>2017-03-31 04:23:53.140000</create_time></item><item><pid>1659</pid><exe>/usr/sbin/NetworkManager</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:53.300000</create_time></item><item><pid>1666</pid><exe>/usr/sbin/modem-manager</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:53.320000</create_time></item><item><pid>1678</pid><exe>/usr/sbin/cupsd</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:53.580000</create_time></item><item><pid>1681</pid><exe>/sbin/dhclient</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:53.670000</create_time></item><item><pid>1686</pid><exe>/usr/sbin/wpa_supplicant</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:53.780000</create_time></item><item><pid>1709</pid><exe>/usr/sbin/acpid</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:53.920000</create_time></item><item><pid>1718</pid><exe>/usr/sbin/hald</exe><status>sleeping</status><user>haldaemon</user><create_time>2017-03-31 04:23:53.970000</create_time></item><item><pid>1719</pid><exe>/usr/libexec/hald-runner</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:53.980000</create_time></item><item><pid>1751</pid><exe>/usr/libexec/hald-addon-rfkill-killswitch</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:54.440000</create_time></item><item><pid>1762</pid><exe>/usr/libexec/hald-addon-input</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:54.500000</create_time></item><item><pid>1767</pid><exe>/usr/libexec/hald-addon-acpi</exe><status>sleeping</status><user>haldaemon</user><create_time>2017-03-31 04:23:54.520000</create_time></item><item><pid>1783</pid><exe>/usr/sbin/bluetoothd</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:54.810000</create_time></item><item><pid>1815</pid><exe>/usr/sbin/sshd</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:55.020000</create_time></item><item><pid>1831</pid><exe>kernel thread</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:55.070000</create_time></item><item><pid>1866</pid><exe>/usr/lib/vmware-tools/bin64/appLoader</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:55.520000</create_time></item><item><pid>1946</pid><exe>/usr/libexec/postfix/master</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:56.220000</create_time></item><item><pid>1958</pid><exe>/usr/libexec/postfix/qmgr</exe><status>sleeping</status><user>postfix</user><create_time>2017-03-31 04:23:56.270000</create_time></item><item><pid>1970</pid><exe>/usr/sbin/abrtd</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:56.410000</create_time></item><item><pid>1978</pid><exe>/usr/sbin/crond</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:56.510000</create_time></item><item><pid>1990</pid><exe>/usr/sbin/atd</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:56.870000</create_time></item><item><pid>2009</pid><exe>/bin/login</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:57.160000</create_time></item><item><pid>2011</pid><exe>/sbin/mingetty</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:57.190000</create_time></item><item><pid>2013</pid><exe>/sbin/mingetty</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:57.200000</create_time></item><item><pid>2017</pid><exe>/sbin/mingetty</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:57.250000</create_time></item><item><pid>2019</pid><exe>/sbin/mingetty</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:57.270000</create_time></item><item><pid>2021</pid><exe>/sbin/mingetty</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:23:57.310000</create_time></item><item><pid>2088</pid><exe>/sbin/udevd</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:25:42.050000</create_time></item><item><pid>2089</pid><exe>/sbin/udevd</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:25:42.050000</create_time></item><item><pid>2105</pid><exe>/usr/sbin/console-kit-daemon</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:26:06.990000</create_time></item><item><pid>2172</pid><exe>/bin/bash</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:26:07.110000</create_time></item><item><pid>2200</pid><exe>/usr/sbin/sshd</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:29:13.600000</create_time></item><item><pid>2205</pid><exe>/bin/bash</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:29:17.290000</create_time></item><item><pid>2292</pid><exe>/usr/sbin/sshd</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:39:15.650000</create_time></item><item><pid>2296</pid><exe>/bin/bash</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 04:39:18.060000</create_time></item><item><pid>3139</pid><exe>/usr/libexec/postfix/pickup</exe><status>sleeping</status><user>postfix</user><create_time>2017-03-31 05:54:59.870000</create_time></item><item><pid>3245</pid><exe>/usr/bin/python</exe><status>sleeping</status><user>root</user><create_time>2017-03-31 06:03:49.990000</create_time></item><item><pid>3359</pid><exe>/usr/bin/python</exe><status>running</status><user>root</user><create_time>2017-03-31 06:24:38.400000</create_time></item></message></Response>';
            $socket_response = new \SimpleXMLElement($xml);*/


            if( strtolower($socket_response->result)=='success' ) {
                $processes = $socket_response->message;
                $processes = json_decode(json_encode($processes), true);
                $table_headers = ['pid','exe','status','user','create_time'];
                $table_rows = $processes['item'];
                
                $process_info = '';
                $process_box = new Box('进程信息', new Table($table_headers, $table_rows));
                $tab->add('进程信息', $process_box, 'process');
            }

            $network_html = '';
            foreach ($networks as $eth => $info) {
                $network_html .= '<div class="container" style="border:1px #818181 solid;"><div class="col-xs-4">' . $eth . '</div><div class="col-xs-8">';
                foreach ($info as $arg => $value) {
                    $network_html .= $arg . ':' . $value . '<br>';
                }
                $network_html .= '</div></div>';
            }
            
            $network_box = new Box('网络信息', $network_html);
            $tab->add('网络信息', $network_box, 'network');
            
            $content->row($tab);

        });
    }

    /**
     * Edit interface.
     *
     * @param $id
     * @return Content
     */
    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {

            $content->header('header');
            $content->description('description');

            $content->body($this->form()->edit($id));
        });
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {

            $content->header('header');
            $content->description('description');

            $content->body($this->form());
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(Platform::class, function (Grid $grid) {

            $grid->id('ID')->sortable();

            $grid->platform_name('主机名')->editable();
            $grid->platform_ip('IP地址')->editable();

            $grid->platform_sn('序列号');
            $grid->column('alive', '状态')->display(function($alive) {
                return $alive==1?'<span class="label label-success">Alive</span>':'<span class="label label-danger">Dead</span>';
            });

            $grid->created_at('添加时间');
            // $grid->updated_at();

            $grid->actions(function ($actions) {
                $id = $actions->getKey();
                $actions->disableEdit();
                $actions->disableDelete();
                $actions->append('<a href="platform/' . $id . '">查看</a>');
                /*$actions->append('&nbsp;|&nbsp;<a href="platform-process/' . $id . '">策略</a>');
                $actions->append('&nbsp;|&nbsp;<a href="platform-files/' . $id . '">文件</a>');*/
            });

            $grid->tools(function ($tools) {
                $tools->batch(function ($batch) {
                    $batch->disableDelete();
                });
            });
            $grid->disableCreation();
            $grid->disableFilter();
            $grid->disableExport();
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(Platform::class, function (Form $form) {

            $form->display('id', 'ID');

            $form->text('platform_name', '主机名');
            $form->text('platform_ip', 'IP地址');
            $form->text('platform_sn', '序列号');
            $form->text('alive', '状态');

            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');
        });
    }

}
