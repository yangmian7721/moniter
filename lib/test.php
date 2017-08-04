<?php
include_once 'open-falcon_lib.php';
//var_dump(get_counter_id('App.centos', 'agent.alive'));
//var_dump(get_chart_data(get_counter_id('SquidMaster.centos','squidonlineclients')['id'], time() - 3600, time()));
//var_dump(get_data_last('SquidMaster.centos', 'squidonlineclients'));
//var_dump(get_value_range('itRoomEnvironment-admin', 'devtempvalue',time()-360, time()));
var_dump(get_data_last_v2('SquidMaster.centos', 'net.if.total.bytes/iface=eth1'));
/*
$curl = curl_init();
$cookie_jar = tempnam(sys_get_temp_dir(),'cookie');

curl_setopt($curl, CURLOPT_URL,'http://192.168.99.26:8081/auth/login');//这里写上处理登录的界面
curl_setopt($curl, CURLOPT_POST, 1);
$request = 'name=rh.wang&password=qifu@123';
curl_setopt($curl, CURLOPT_POSTFIELDS, $request);//传 递数据
curl_setopt($curl, CURLOPT_COOKIEJAR, $cookie_jar);// 把返回来的cookie信息保存在$cookie_jar文件中
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);//设定返回 的数据是否自动显示
curl_setopt($curl, CURLOPT_HEADER, false);//设定是否显示头信 息
curl_setopt($curl, CURLOPT_NOBODY, false);//设定是否输出页面 内容
$rs = curl_exec($curl);//返回结果
var_dump($rs,$cookie_jar);
curl_close($curl); //关闭
*/
/*
$url = 'http://192.168.99.26:8080/api/v1/graph/history';
$data = array("step" => 60,
              "start_time"  => time()-3600,
              "hostnames"   => ['SquidMaster.centos'],
              "end_time"    => time(),
              "counters"    => ["cpu.idle"],
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
var_dump(json_decode($content, true));
*/
/*
var_dump( get_counter_id('App.centos', 'agent.alive',$cookie_jar) );

unlink($cookie_jar);

function get_counter_id($endpoints, $counters, $cookie_jar)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://192.168.99.26:8081/chart');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'endpoints%5B%5D='.$endpoints.'&counters%5B%5D='.$counters.'&graph_type=h');
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
    $response = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($response, true);

    return $result;
}
*/
