<?php
function image_resize($source, $dst_w,$dst_h)
{
    $width=  imagesx($source);
    $height=  imagesy($source);
    $aspectRatio=$width/$height;
    $w=0;$h=0;$x=0;$y=0;
    if($aspectRatio>=1.0) 
    {
        $w=$dst_w;
        $h=  floor($w/$aspectRatio);
    }
    else
    {
        $h=$dst_h;
        $w=floor($h*$aspectRatio);
    }
    $x=floor(($dst_w-$w)/2);
    $y=floor(($dst_h-$h)/2);
    $destination=  imagecreatetruecolor($dst_w,$dst_h);
    imagealphablending($destination, false);
    imagesavealpha($destination, true);
    $transparent = imagecolorallocatealpha($destination, 255, 255, 255, 127);
    imagefilledrectangle($destination, 0, 0, $dst_w, $dst_h, $transparent);
    imagecopyresampled($destination, $source, $x,$y,0,0,$w,$h,$width,$height);
    return $destination;
}
?>
