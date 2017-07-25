<?php
/**
 * 获取指定counter的信息
 * @param $endpoints 主机名
 * @param $counters 相应的counters
 * @return Array[params, ok, id]
 */
function get_counter_id($endpoints, $counters)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://192.168.99.27:8081/chart');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'endpoints%5B%5D='.$endpoints.'&counters%5B%5D='.$counters.'&graph_type=h');
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
    $response = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($response, true);

    return $result;
}

/**
 * 获取指定counter_id的数据
 * @param $counter_id
 * @param $start 起始时间戳
 * @param $end 结束时间戳
 * @return Array[units, series[0[counter,endpoint,data[0],name,cf]], title]
 */
function get_chart_data($counter_id, $start, $end)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://192.168.99.27:8081/chart/k?id='.$counter_id.'&cf=AVERAGE&start='.$start.'&end='.$end.'&sum=off');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($response, true);

    return $result;
}
/**
 * 获取指定counter的最后数据
 * @param $endpoints
 * @param $counter
 * @return Array[endpoint, counter,value[timestamp, value]]
 */
function get_data_last($endpoints, $counter)
{
    $data = array('endpoint' => $endpoints , 'counter' => $counter);
    $post_data = "[".json_encode($data)."]";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://192.168.99.27:9966/graph/last");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      "Content-Type: application/json"
    ));
    $response = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($response, true);

    return $result;
}

function get_value_last($endpoints, $counter)
{
  $data = get_data_last($endpoints, $counter);
  $value  = $data[0]['value']['value'];

  return $value;
}

function get_value_range($endpoints, $counter)
{
  $x = array();
  $y = array();
  $counter_id = get_counter_id($endpoints, $counter)['id'];
  $datas = get_chart_data($counter_id, time() - 3600, time());
  foreach ($datas['series'][0]['data'] as $k => $value) {
      if ($value[1] === null) {
          $value[1] = '-';
      }
      $y[] = $value[1];
      $x[] = date('H:i', $value[0] / 1000);
  }

  return array('x' => $x, 'y' => $y );
}

function get_online()
{
  $master = get_value_last('SquidMaster.centos', 'squidonlineclients');
  $salve = get_value_last('SquidSlave.centos', 'squidonlineclients');
  $online  = $master + $salve;

  return $online;
}

function get_connects()
{
  $master = get_value_last('SquidMaster.centos', 'ss.estab');
  $salve = get_value_last('SquidSlave.centos', 'ss.estab');
  $connects  = $master + $salve;

  return $connects;
}

function get_bandwidth()
{
  $master = get_value_last('SquidMaster.centos', 'net.if.total.bytes/iface=eth1');
  $salve = get_value_last('SquidSlave.centos', 'net.if.total.bytes/iface=eth1');
  $connects  = $master + $salve;

  return $connects;
}

$online = get_online();
$connects = get_connects();
$bandwidth = get_bandwidth();
$load_squid = (get_value_last('SquidMaster.centos', 'load.1min') + get_value_last('SquidSlave.centos', 'load.1min'));
$load_isc = get_value_last('ISC.centos', 'load.1min');
$load_mysql = (get_value_last('Mysql_cacti.centos', 'load.1min') + get_value_last('Mysql.centos', 'load.1min'));
$load = array('load_squid' => $load_squid ,'load_isc' => $load_isc ,'load_mysql' => $load_mysql);

$io_squid = (get_value_last('SquidMaster.centos', 'disk.io.util/device=sda') + get_value_last('SquidSlave.centos', 'disk.io.util/device=sda'));
$io_isc = get_value_last('ISC.centos', 'disk.io.util/device=sda') ;
$io_mysql = (get_value_last('Mysql_cacti.centos', 'disk.io.util/device=sda') + get_value_last('Mysql.centos', 'disk.io.util/device=sda'));
$io = array('io_squid' => $io_squid ,'io_isc' => $io_isc ,'io_mysql' => $io_mysql);

$ram_squid = (get_value_last('SquidMaster.centos', 'mem.memused.percent') + get_value_last('SquidSlave.centos', 'mem.memused.percent')) / 2;
$ram_isc = get_value_last('ISC.centos', 'mem.memused.percent') ;
$ram_mysql = (get_value_last('Mysql_cacti.centos', 'mem.memused.percent') + get_value_last('Mysql.centos', 'mem.memused.percent')) / 2;
$ram = array('ram_squid' => $ram_squid ,'ram_isc' => $ram_isc ,'ram_mysql' => $ram_mysql);

$onlines_master = get_value_range('SquidMaster.centos', 'squidonlineclients');
$onlines_standby = get_value_range('SquidSlave.centos','squidonlineclients');
$onlines = array('data' => array('master' => $onlines_master, 'standby' => $onlines_standby), 'title' => '代理服务器人数', 'legend' => array('Master','Standby'));

$conn_master = get_value_range('SquidMaster.centos','ss.estab');
$conn_standby = get_value_range('SquidSlave.centos','ss.estab');
$conn = array('data' => array('master' => $conn_master, 'standby' =>$conn_standby), 'title' => '代理服务器连接数', 'legend' => array('Master','Standby') );

$all_datas = array('load' => $load ,'connects' => $connects, 'bandwidth' => $bandwidth, 'online' => $online ,'io' => $io, 'ram' => $ram, 'onlines' => $onlines, 'conn' => $conn);
echo json_encode($all_datas);

//var_dump($all_datas);
//var_dump(get_value_last('ISC.centos','load.1min'));
//var_dump(get_data_last('SquidMaster.centos', 'squidonlineclients'));
//print_r(get_chart_data(get_counter_id('SquidMaster.centos','squidonlineclients')['id'], time() - 3600, time()));
