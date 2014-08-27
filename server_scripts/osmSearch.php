<?php
$query=  urlencode($_GET['q']);
header('Content-Type: application/json; charset=utf-8');
$response['nominatium']=file_get_contents("http://www.openstreetmap.org/geocoder/search_osm_nominatim?maxlat=90.0&maxlon=180.0&minlat=-90.0&minlon=-180&query=$query");
$response['geonames']=file_get_contents("http://www.openstreetmap.org/geocoder/search_geonames?maxlat=90.0&maxlon=180.0&minlat=-90.0&minlon=-180&query=$query");
echo json_encode($response);
?>
