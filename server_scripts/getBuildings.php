<?php 
 ob_start();
 header('Content-Type: application/json; utf-8');

    require_once("config.php");

    $position_lon =$_GET['minlon'];
    $position_lonend =$_GET['maxlon'];
    $position_lat = $_GET['minlat'];
    $position_latend = $_GET['maxlat'];
    $tile_id = $_GET['tile_id']; 
    $fullarr = array();

$query= <<<EOD
SELECT * FROM objectInstance WHERE positionLon >=  '$position_lon' AND positionLon <=  '$position_lonend' 
AND positionLat >= '$position_lat' AND positionLat <= '$position_latend';
EOD;

	$result = mysql_query($query);
    if(!$result)exit("Ошибка - ".mysql_error());
	
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) 
	{
        //printf ("ID: %s  Name: %s", $row["id"], $row["name"]);
        $t1 = $row["id"];
        $t2 = $row["scaleX"];
        $t3 = $row["scaleY"];
        $t4 = $row["scaleZ"];
        $t5 = $row["rotationX"];
        $t7 = $row["rotationY"];
        $t8 = $row["rotationZ"];
        $t9 = $row["positionLon"];
        $t10 = $row["positionLat"];
        $t11 = $row["positionHeight"];
        $t12 = $row["TypeID"];
        $fullarr[] = array("id" => $t1, "scaleX" => $t2, "scaleY" => $t3, "scaleZ" => $t4, "rotationX" => $t5,
         "rotationY" => $t7, "rotationZ" => $t8, "positionLon" => $t9, "positionLat" => $t10, "positionHeight" => $t11, 
         "TypeID" => $t12);
    }
    mysql_free_result($result);
	ob_end_flush();
	$json_data = array('tile_id' => $tile_id,'builds' => $fullarr);
	echo json_encode($json_data);
?>
