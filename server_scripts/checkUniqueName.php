<?php
//require_once 'config.php';
$name=$_POST['name'];

$link = mysql_connect('127.0.0.1', 'root', '');
if (!$link) {
    echo "Error";
    die('Ошибка соединения: ' . mysql_error());
}

$db_selected = mysql_select_db('osmex3d', $link);
if (!$db_selected) {
    echo "Die!";
    die('Не удалось выбрать базу osmex3d: ' . mysql_error());
}

//if($connection===FALSE || $select_db===FALSE)
//{
//    echo '2';
//    exit;
//}
$q = sprintf("SELECT COUNT(*) FROM objecttype WHERE name='%s'", mysql_real_escape_string($name));
$result = mysql_query($q);
$count = mysql_fetch_array($result);
mysql_free_result($result);
echo $count[0];
mysql_close($link);
?>
