<?php
require_once 'config.php';
require_once 'imageThumbnail.php';
$name = $_POST['name'];
$category = $_POST['category'];
$serializedGeometry = json_decode($_POST['geometry']);
$origScaleX = $_POST['origScaleX'];
$origScaleY = $_POST['origScaleY'];
$origScaleZ = $_POST['origScaleZ'];
$imageData = $_POST['imageData'];
if($connection===FALSE||$select_db===FALSE)
    die('MySQL connection error.');
$q = sprintf("SELECT COUNT(*) FROM objecttype WHERE name='%s'", mysql_real_escape_string($name));
$result = mysql_query($q);
$count = mysql_fetch_array($result);
mysql_free_result($result);
if ($count[0] == 0) {
    
    $q = sprintf("SELECT id FROM objectcategory WHERE name='%s'", mysql_real_escape_string($category));
    $result = mysql_query($q);
    $id = mysql_fetch_array($result);
    if($id===FALSE)
    {
        $query = sprintf("INSERT INTO objectcategory ( id, name ) VALUES(NULL, '%s')", mysql_real_escape_string($category));
        mysql_query($query);
        $id['id']=  mysql_insert_id();
    }
    mysql_free_result($result);
    $i = $id['id'];
   
    
    $query = sprintf("INSERT INTO objecttype ( name, CategoryID, geometryStr, origScaleX, origScaleY, origScaleZ ) VALUES('%s', ".$i.", '%s', ".$origScaleX.", ".$origScaleY.", ".$origScaleZ.")", mysql_real_escape_string($name), mysql_real_escape_string(serialize($serializedGeometry)));
    //echo $query;
    mysql_query($query);
    $uid=  mysql_insert_id();
    $prefix='../'.PREVIEWS_PATH."/".$uid."_".$name;
    $pattern="/data:image\/(png|jpeg|jpg|gif|tiff|tif);base64,(.*)/i";
    if(preg_match($pattern, $imageData,$match))
    {
        $type=$match[1];
        $data=base64_decode($match[2]);
        $handle=  fopen($prefix.".".$type, "w");
        if(!$handle)
            die("Can't save preview image.");
        fwrite($handle, $data);
        fclose($handle);
        $image=imagecreatefromstring($data);
        if($image!==FALSE)
        {
            $thumbnail=  image_resize($image, TWIDTH, THEIGHT);
            imagepng($thumbnail, $prefix.'_mini.png');
        }
    }
    else
    {
        echo "Wrong image data format.\n";
    }
    echo "Saving completed.";
    
} else {
    echo "Error.\nYou need to change figure's name.";
}
mysql_close($connection);
?>
