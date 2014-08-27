<?php
require_once 'config.php';
require_once 'imageThumbnail.php';
$body = file_get_contents('php://input');
$pack=  json_decode($body,true);
if($pack===FALSE || $pack===NULL)
{
    $response['success']=false;
    $response['message']="Error: can't decode data.";
    echo json_encode($response);
    exit;
}
if($connection===FALSE || $select_db===FALSE)
{
    $response['success']=false;
    $response['message']="Database error.";
    echo json_encode($response);
    exit;
}
if(count($pack)==0)
{
    $response['success']=false;
    $response['message']="Nothing to save.";
    echo json_encode($response);
    exit;
}
$response['textures']=array();
foreach ($pack as $region) {
    $textureName=  mysql_real_escape_string($region['name']);
    $texturePoints=  mysql_real_escape_string(serialize($region['points']));
    $query="INSERT INTO textures (TextureName,TexturePoints) VALUES ('$textureName','$texturePoints')";
    $r=mysql_query($query);
    if($r===FALSE)
    {
        $response['success']=false;
        $response['message']="Error: can't add textures to database. ".mysql_error();
        echo json_encode($response);
        exit;
    }
    $uid=  mysql_insert_id();
    //mysql_close($connection);
    $prefix='../'.TEXTURE_PATH."/".$uid."_".$region['name'];
    $pattern="/data:image\/(png|jpeg|jpg|gif|tiff|tif);base64,(.*)/i";
    if(preg_match($pattern, $region['dataurl'],$match))
    {
        $type=$match[1];
        $data=base64_decode($match[2]);
        $handle=  fopen($prefix.".".$type, "w");
        if(!$handle)
        {
            $response['success']=false;
            $response['message']="Error: can't save texture files.";
            echo json_encode($response);
            exit;
        }
        fwrite($handle, $data);
        fclose($handle);
        $image=imagecreatefromstring($data);
        if($image!==FALSE)
        {
            $thumbnail=  image_resize($image, 96, 96);
            imagepng($thumbnail, $prefix.'_mini.png');
        }
        $tmp['uid']=$uid;
        $tmp['name']=$region['name'];
        $tmp['thumbnail']=TEXTURE_PATH.'/'.$uid.'_'.$region['name'].'_mini.png';
        $tmp['image']=TEXTURE_PATH.'/'.$uid.'_'.$region['name'].'.png';
        $response['textures'][]=$tmp;  
    }
    else
    {
        $response['success']=false;
        $response['message']="Error: unknown data url format.";
        echo json_encode($response);
        exit;
    }
}
$response['success']=true;
$response['message']="Saving completed.";
echo json_encode($response);
?>