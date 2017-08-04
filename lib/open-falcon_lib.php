<?php
define('NAME', 'rh.wang');
define('PASSWORD', 'qifu@123');
define('ADDR_DASHBOAR', 'http://192.168.99.26:8081');
define('ADDR_API', 'http://192.168.99.26:8080');

//v0.2版本直接使用api获取数据
function get_data_history_v2($endpoint, $counter, $start, $end)
{
  $url = ADDR_API.'/api/v1/graph/history';
  $data = array("step" => 30,
                "start_time"  => $start,
                "hostnames"   => [$endpoint],
                "end_time"    => $end,
                "counters"    => [$counter],
                "consol_fun"  => "AVERAGE"
            );
  $data_string = json_encode($data);
  $headers = array();
  $headers[] = 'Apitoken:name=rh.wang;sig=';
  $headers[] = 'Content-Type:application/json';
  $headers[] = 'X-Forwarded-For:127.0.0.1';
  $curl2 = curl_init();
  curl_setopt($curl2, CURLOPT_URL, $url);
  curl_setopt($curl2, CURLOPT_POST, TRUE);
  curl_setopt($curl2, CURLOPT_POSTFIELDS,$data_string);
  curl_setopt($curl2, CURLOPT_HEADER, false);
  curl_setopt($curl2, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($curl2, CURLOPT_RETURNTRANSFER, 1);
  $content = curl_exec($curl2);

  return json_decode($content, true);
}

function get_data_last_v2($endpoint, $counter)
{
  $data_range = get_data_history_v2($endpoint,$counter,time()-150,time());
  $values = array_reverse($data_range[0]['Values']);
  for ($i=0; $i < count($values) - 1; $i++) {
    if ($values[$i]['value'] != NULL) {
      $last = $values[$i]['value'];
      break;
    }
  }
  return $last;
}

//v0.2版本中读取到数据后拼装成echarts的数据结构
function get_data_history_echarts($endpoints, $counter, $start, $end)
{
  $x = array();
  $y = array();
  $datas = get_data_history_v2($endpoints, $counter, $start, $end);
  foreach ($datas[0]['Values'] as $k => $value) {
      if ($value[1] === null) {
          $value[1] = '-';
      }
      $y[] = $value['value'];
      $x[] = date('H:i', $value['timestamp']);
  }

  return array('x' => $x, 'y' => $y );
}


/*以下函数在dashboard中操作时用到*/

//在v0.2版本中获取dashboard的数据时要验证,所以先登录
function login()
{
  $curl = curl_init();
  $cookie_jar = tempnam(sys_get_temp_dir(),'cookie');
  curl_setopt($curl, CURLOPT_URL, ADDR_DASHBOARD.'/auth/login');
  curl_setopt($curl, CURLOPT_POST, 1);
  $request = 'name='.NAME.'&password='.PASSWORD;
  curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
  curl_setopt($curl, CURLOPT_COOKIEJAR, $cookie_jar);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($curl, CURLOPT_HEADER, false);
  curl_setopt($curl, CURLOPT_NOBODY, false);
  curl_exec($curl);
  curl_close($curl);
  return $cookie_jar;
}


//v0.2通过dashboard获取counter的id值
function get_counter_id($endpoints, $counters)
{
    $cookie_jar = login();
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, ADDR_DASHBOARD.'/chart');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'endpoints%5B%5D='.$endpoints.'&counters%5B%5D='.$counters.'&graph_type=h');
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
    $response = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($response, true);
    unlink($cookie_jar);
    return $result;
}

//通过dashboard 根据counter_id获取数据
function get_chart_data($counter_id, $start, $end)
{
    $cookie_jar = login();
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, ADDR_DASHBOARD.'/chart/k?id='.$counter_id.'&cf=AVERAGE&start='.$start.'&end='.$end.'&sum=off');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);
    $response = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($response, true);
    unlink($cookie_jar);

    return $result;
}

//v0.1版本中获取最后一次数据
function get_data_last($endpoints, $counter)
{

    $data = array('endpoint' => $endpoints , 'counter' => $counter);
    $post_data = "[".json_encode($data)."]";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://192.168.99.27:9966/api/v1/graph/last");
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

    return $result[0]['value']['value'];
}
