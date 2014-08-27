<?php
global $connection,$select_db;
$connection = mysql_connect('localhost', 'root', '');
$select_db = mysql_select_db("osmex3d");
?>