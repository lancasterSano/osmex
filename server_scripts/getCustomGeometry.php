<?php
header('Content-Type: application/json; utf-8');
require_once("config.php");
$rID = $_GET['id'];
$query =  sprintf("SELECT name, geometryStr, origScaleX, origScaleY, origScaleZ FROM objectType WHERE id=".$rID."");
$result = mysql_query($query);
$str = mysql_fetch_array($result);
mysql_free_result($result);
$name = $str['name'];
$geometry = $str['geometryStr'];
$origScaleX = $str['origScaleX'];
$origScaleY = $str['origScaleY'];
$origScaleZ = $str['origScaleZ'];
$json_data = array('name' => $name, 'geometryStr' => unserialize($geometry), 'origScaleX' => $origScaleX, 'origScaleY' => $origScaleY, 'origScaleZ' => $origScaleZ);
echo json_encode($json_data);
mysql_close($connection);
?>
