<?php
include_once 'open-falcon_lib.php';

function get_online()
{
  $master = get_data_last_v2('SquidMaster.centos', 'squidonlineclients');
  $salve = get_data_last_v2('SquidSlave.centos', 'squidonlineclients');
  $online  = $master + $salve;

  return $online;
}

function get_connects()
{
  $master = get_data_last_v2('SquidMaster.centos', 'ss.estab');
  $salve = get_data_last_v2('SquidSlave.centos', 'ss.estab');
  $connects  = $master + $salve;

  return $connects;
}

function get_bandwidth()
{
  $master = get_data_last_v2('SquidMaster.centos', 'net.if.total.bytes/iface=eth1');
  $salve = get_data_last_v2('SquidSlave.centos', 'net.if.total.bytes/iface=eth1');
  $connects  = $master + $salve;

  return $connects;
}

$online = get_online();
$connects = get_connects();
$bandwidth = get_bandwidth();
$load_squid = (get_data_last_v2('SquidMaster.centos', 'load.1min') + get_data_last_v2('SquidSlave.centos', 'load.1min'));
$load_isc = get_data_last_v2('ISC.centos', 'load.1min');
$load_mysql = (get_data_last_v2('Cacti_DB.centos', 'load.1min') + get_data_last_v2('Mysql.centos', 'load.1min'));
$load = array('load_squid' => $load_squid ,'load_isc' => $load_isc ,'load_mysql' => $load_mysql);

$io_squid = (get_data_last_v2('SquidMaster.centos', 'disk.io.util/device=sda') + get_data_last_v2('SquidSlave.centos', 'disk.io.util/device=sda'));
$io_isc = get_data_last_v2('ISC.centos', 'disk.io.util/device=sda') ;
$io_mysql = (get_data_last_v2('Cacti_DB.centos', 'disk.io.util/device=sda') + get_data_last_v2('Mysql.centos', 'disk.io.util/device=sda'));
$io = array('io_squid' => $io_squid ,'io_isc' => $io_isc ,'io_mysql' => $io_mysql);

$ram_squid = (get_data_last_v2('SquidMaster.centos', 'mem.memused.percent') + get_data_last_v2('SquidSlave.centos', 'mem.memused.percent')) / 2;
$ram_isc = get_data_last_v2('ISC.centos', 'mem.memused.percent') ;
$ram_mysql = (get_data_last_v2('Cacti_DB.centos', 'mem.memused.percent') + get_data_last_v2('Mysql.centos', 'mem.memused.percent')) / 2;
$ram = array('ram_squid' => $ram_squid ,'ram_isc' => $ram_isc ,'ram_mysql' => $ram_mysql);

$onlines_master = get_data_history_echarts('SquidMaster.centos', 'squidonlineclients', (time()-3600), time());
$onlines_standby = get_data_history_echarts('SquidSlave.centos','squidonlineclients', (time()-3600), time());
$onlines = array('data' => array('master' => $onlines_master, 'standby' => $onlines_standby), 'title' => '代理服务器人数', 'legend' => array('Master','Standby'));

$conn_master = get_data_history_echarts('SquidMaster.centos','ss.estab', (time()-3600), time());
$conn_standby = get_data_history_echarts('SquidSlave.centos','ss.estab', (time()-3600), time());
$conn = array('data' => array('master' => $conn_master, 'standby' =>$conn_standby), 'title' => '代理服务器连接数', 'legend' => array('Master','Standby') );

$all_datas = array('load' => $load ,'connects' => $connects, 'bandwidth' => $bandwidth, 'online' => $online ,'io' => $io, 'ram' => $ram, 'onlines' => $onlines, 'conn' => $conn);
echo json_encode($all_datas);

//var_dump($all_datas);
//var_dump(get_data_last_v2('ISC.centos','load.1min'));
//var_dump(get_data_last('SquidMaster.centos', 'squidonlineclients'));
//print_r(get_chart_data(get_counter_id('SquidMaster.centos','squidonlineclients')['id'], time() - 3600, time()));
