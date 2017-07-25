<?php
$host = "192.168.99.22";
$db_user = "root";
$db_pass = "viproot";
$db_name = "cacti_1.0_1";
$timezone = "Asia/Shanghai";
$link = mysql_connect($host, $db_user, $db_pass);
mysql_select_db($db_name, $link);
?>
