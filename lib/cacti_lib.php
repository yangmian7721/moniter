<?php
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
