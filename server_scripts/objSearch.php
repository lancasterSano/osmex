<?php
require_once 'config.php';
$req = $_GET['q'];
global $array;
if($req=="")
{
$sql = "SELECT Cat.name as 'nameCat', Type.name as 'nameType', Type.id as idType FROM objectcategory Cat
        INNER JOIN objecttype Type
        ON Type.CategoryID = Cat.id
        ORDER BY Cat.name, Type.name ASC";
}
else
{
    $sql = "SELECT Cat.name as 'nameCat', Type.name as 'nameType', Type.id as idType FROM objectcategory Cat
        INNER JOIN objecttype Type
        ON Type.CategoryID = Cat.id
        WHERE Type.name LIKE '%".  mysql_real_escape_string($req)."%';";
}
$query = mysql_query($sql, $connection);
while ($row = mysql_fetch_array($query)) {
    $test['name'] = $row['nameType'];
    $test['previewFileName'] = $row['idType'].'_'.$row['nameType'];
    $test['id']=$row['idType'];
    $array[$row['nameCat']][]=$test;
}
mysql_close($connection);
if(sizeof($array)<=0)
{
    echo "no objects found";
    return;
}
foreach ($array as $nameFigureType => $instances) {
echo '<div objectcategory="'.$nameFigureType.'" class="flip ui-widget ui-widget-header ui-corner-all">'.$nameFigureType.'('.sizeof($instances).')</div>';                           
echo '<div class="slidingPanel ui-widget ui-widget-content ui-corner-all" style="display:none;">';
    for($i=0;$i<sizeof($instances);$i++)
    {
        echo '<div class=imgContainer id="'.$instances[$i]['id'].'">';
        echo '<img class="prev" src="previews/'.$instances[$i]['previewFileName'].'_mini.png">';
        echo '<div class=desc>';
        echo $instances[$i]['name'];
        echo '</div></div>';
    }
    echo '</div>';   
}

?>
