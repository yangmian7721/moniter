<?php
ob_start('ob_gzhandler');
function rrd($file, $datakey = 0, $num_digits = 2, $step = '360', $start = '-6h', $end = 'start+5h+54m', $xtype = 'H:i')
    {
        $rrd = rrd_fetch($file, array('AVERAGE', '--resolution', $step, '--start', $start, '--end', $end));
        $filekey = array_keys($rrd['data'])[$datakey];
        $data = $rrd['data'][$filekey];
        foreach ($data as $k => $v) {
            $xAxis[] = date($xtype, $k);
            if (is_nan($v)||$v == 0) {
                //当接受到的数据为NAN时就要加这个判断 否则 json_encode时会出错
                  $yAxis[] = '-';
            } else {
                $yAxis[] = round($v, $num_digits);
            }
        }
        $result['xAxis'] = $xAxis;
        $result['yAxis'] = $yAxis;

        return $result;
    }
function Get_rrd_path($hostname,$name_cache)
{
  require_once 'connect.php';
  $sql_where = "SELECT `dtd`.data_source_path,`dt`.unit
                FROM data_local as dl,host,data_template as dt,data_template_data as dtd
                WHERE `dl`.host_id=`host`.id
                AND `dt`.id=`dl`.data_template_id
                AND `dl`.id=`dtd`.local_data_id
                AND `host`.hostname = '$hostname'
                AND `dtd`.name_cache LIKE '%$name_cache'
                LIMIT 1";
  $query = mysql_query($sql_where);
  while ($rows = mysql_fetch_array($query)) {
    $rrd_path = $rows['data_source_path'];
  }
  return str_replace('<path_rra>','/mnt/rra',$rrd_path);
}

function HostDown(){
	$time = date("H:i:s");
	$results = "";
    $hostdowns = mysql_fetch_array(mysql_query('SELECT COUNT(*) FROM host WHERE status = 1 AND disabled="" '));
    $downlists= mysql_query('SELECT * FROM host WHERE status = 1 AND disabled="" ');
        while ($row = mysql_fetch_array($downlists)) {
            $results .= " " . $row['description'] . " [" . $row['hostname'] . "] ";
        }
    if (!$hostdowns[0]) {
      $results = "满分";
    }
    $msg .= $results." ".$time;
    $msgs = array('msg' =>$msg ,'time' =>$time,'downs' =>$hostdowns[0] );
    return $msgs;
}
    $core = rrd(Get_rrd_path('10.255.255.254','availability-core'), 0, 4, '3600',  '-30d',  'start+29d+23h', 'm-d');
    $vpn = rrd(Get_rrd_path('10.255.255.254','availability-vpn'), 0, 4, '3600',  '-30d',  'start+29d+23h', 'm-d');
    $access = rrd(Get_rrd_path('10.255.255.254','availability-access'), 0, 4, '3600',  '-30d',  'start+29d+23h', 'm-d');

    $stability = array();
    $stability['xAxis'] = $core['xAxis'];
    $stability['vpn'] = $vpn['yAxis'];
    $stability['access'] = $access['yAxis'];
    $stability['core'] = $core['yAxis'];
    $stability['legend'] = ['核心层', '接入层', 'VPN'];

    $water = rrd(Get_rrd_path('10.255.255.253','温度槽0-主'));
    $joyful = rrd(Get_rrd_path('10.255.255.252','温度槽1'));
    $admin = rrd(Get_rrd_path('10.255.255.254','温度槽0-主'));
    $club = rrd(Get_rrd_path('10.255.255.251','温度'));
    $mingdu = rrd(Get_rrd_path('10.255.255.249','温度'));
    $temperature = array();
    $temperature['xAxis'] = $water['xAxis'];
    $temperature['water'] = $water['yAxis'];
    $temperature['joyful'] = $joyful['yAxis'];
    $temperature['admin'] = $admin['yAxis'];
    $temperature['club'] = $club['yAxis'];
    $temperature['mingdu'] = $mingdu['yAxis'];
    $temperature['legend'] = ['海晴居', '晋福楼', '行政楼', '俱乐部', '名都'];
    //echo Get_rrd_path('172.18.200.253','GigabitEthernet0/0/1');

    $lt_in = rrd(Get_rrd_path('172.18.200.253','GigabitEthernet0/0/1'), 0, 0);
    $lt_out = rrd(Get_rrd_path('172.18.200.253','GigabitEthernet0/0/1'), 1, 0);
    $dx_in = rrd(Get_rrd_path('192.168.99.240','GigabitEthernet0/0/4'), 0, 0);
    $dx_out = rrd(Get_rrd_path('192.168.99.240','GigabitEthernet0/0/4'), 1, 0);
    $outside = array();
    $outside['xAxis'] = $lt_in['xAxis'];
    $outside['lt_in'] = $lt_in['yAxis'];
    $outside['lt_out'] = $lt_out['yAxis'];
    $outside['dx_in'] = $dx_in['yAxis'];
    $outside['dx_out'] = $dx_out['yAxis'];

    $outside['legend'] = ['电信-下载', '电信-上传', '联通-下载', '联通-上传'];


    $traffic_254_trunk_0_in = rrd(Get_rrd_path('10.255.255.254','Eth-Trunk0'), 0, 0);
    $traffic_254_trunk_1_in = rrd(Get_rrd_path('10.255.255.254','Eth-Trunk1'), 0, 0);
    $traffic_254_trunk_2_in = rrd(Get_rrd_path('10.255.255.254','Eth-Trunk2'), 0, 0);
    $traffic_254_trunk_3_in = rrd(Get_rrd_path('10.255.255.254','Eth-Trunk3'), 0, 0);
    $traffic_254_trunk_4_in = rrd(Get_rrd_path('10.255.255.254','Eth-Trunk4'), 0, 0);
    $traffic_254_trunk_0_out = rrd(Get_rrd_path('10.255.255.254','Eth-Trunk0'), 1, 0);
    $traffic_254_trunk_1_out = rrd(Get_rrd_path('10.255.255.254','Eth-Trunk1'), 1, 0);
    $traffic_254_trunk_2_out = rrd(Get_rrd_path('10.255.255.254','Eth-Trunk2'), 1, 0);
    $traffic_254_trunk_3_out = rrd(Get_rrd_path('10.255.255.254','Eth-Trunk3'), 1, 0);
    $traffic_254_trunk_4_out = rrd(Get_rrd_path('10.255.255.254','Eth-Trunk4'), 1, 0);

    $traffic_253_trunk_0_in = rrd(Get_rrd_path('10.255.255.253','Eth-Trunk0'), 0, 0);
    $traffic_253_trunk_1_in = rrd(Get_rrd_path('10.255.255.253','Eth-Trunk1'), 0, 0);
    $traffic_253_trunk_0_out = rrd(Get_rrd_path('10.255.255.253','Eth-Trunk0'), 1, 0);
    $traffic_253_trunk_1_out = rrd(Get_rrd_path('10.255.255.253','Eth-Trunk1'), 1, 0);

    $traffic_252_trunk_0_in = rrd(Get_rrd_path('10.255.255.252','Eth-Trunk0'), 0, 0);
    $traffic_252_trunk_0_out = rrd(Get_rrd_path('10.255.255.252','Eth-Trunk0'), 1, 0);

    $traffic_251_trunk_0_in = rrd(Get_rrd_path('10.255.255.251','Eth-Trunk0'), 0, 0);
    $traffic_251_trunk_0_out = rrd(Get_rrd_path('10.255.255.251','Eth-Trunk0'), 1, 0);

    $traffic_249_trunk_0_in = rrd(Get_rrd_path('10.255.255.249','GigabitEthernet0/0/21'), 0, 0);
    $traffic_249_trunk_0_out = rrd(Get_rrd_path('10.255.255.249','GigabitEthernet0/0/21'), 1, 0);
    $traffic_249_trunk_1_in = rrd(Get_rrd_path('10.255.255.249','GigabitEthernet0/0/23'), 0, 0);
    $traffic_249_trunk_1_out = rrd(Get_rrd_path('10.255.255.249','GigabitEthernet0/0/23'), 1, 0);
    $traffic_249_trunk_2_in = rrd(Get_rrd_path('10.255.255.249','GigabitEthernet0/0/22'), 0, 0);
    $traffic_249_trunk_2_out = rrd(Get_rrd_path('10.255.255.249','GigabitEthernet0/0/22'), 1, 0);

    $traffic_254_in = array();
    $traffic_254_out = array();
    $traffic_253_in = array();
    $traffic_253_out = array();
    $traffic_252_in = array();
    $traffic_252_out = array();
    $traffic_251_in = array();
    $traffic_251_out = array();
    $traffic_249_in = array();
    $traffic_249_out = array();

    foreach ($traffic_254_trunk_0_in['yAxis'] as $key => $value) {
        $traffic_254_in[$key] = $value + $traffic_254_trunk_1_in['yAxis'][$key] + $traffic_254_trunk_2_in['yAxis'][$key] + $traffic_254_trunk_3_in['yAxis'][$key] + $traffic_254_trunk_4_in['yAxis'][$key];
        $traffic_254_out[$key] = $traffic_254_trunk_0_out['yAxis'][$key] + $traffic_254_trunk_1_out['yAxis'][$key] + $traffic_254_trunk_2_out['yAxis'][$key] + $traffic_254_trunk_3_out['yAxis'][$key] + $traffic_254_trunk_4_out['yAxis'][$key];
        $traffic_253_in[$key] = $traffic_253_trunk_0_in['yAxis'][$key] + $traffic_253_trunk_1_in['yAxis'][$key];
        $traffic_253_out[$key] = $traffic_253_trunk_0_out['yAxis'][$key] + $traffic_253_trunk_1_out['yAxis'][$key];
        $traffic_252_in[$key] = $traffic_252_trunk_0_in['yAxis'][$key];
        $traffic_252_out[$key] = $traffic_252_trunk_0_out['yAxis'][$key];
        $traffic_251_in[$key] = $traffic_251_trunk_0_in['yAxis'][$key];
        $traffic_251_out[$key] = $traffic_251_trunk_0_out['yAxis'][$key];
        $traffic_249_in[$key] = $traffic_249_trunk_0_in['yAxis'][$key] + $traffic_249_trunk_1_in['yAxis'][$key] + $traffic_249_trunk_2_in['yAxis'][$key];
        $traffic_249_out[$key] = $traffic_249_trunk_0_out['yAxis'][$key] + $traffic_249_trunk_1_out['yAxis'][$key] + $traffic_249_trunk_2_out['yAxis'][$key];
    }

    $traffic = array();
    $traffic['xAxis'] = $traffic_254_trunk_0_in['xAxis'];
    $traffic['all_254'] = $traffic_254_in + $traffic_254_out;
    $traffic['all_253'] = $traffic_253_in + $traffic_253_out;
    $traffic['all_252'] = $traffic_252_in + $traffic_252_out;
    $traffic['all_251'] = $traffic_251_in + $traffic_251_out;
    $traffic['all_249'] = $traffic_249_in + $traffic_249_out;
    $traffic['legend'] = ['行政楼', '海晴居', '晋福楼', '俱乐部', '名都'];


    $data = array();
    $data['stability'] = $stability;
    $data['temperature'] = $temperature;
    $data['outside'] = $outside;
    $data['inside'] = $traffic;
    $data['downlist'] = HostDown();

    echo json_encode($data);
