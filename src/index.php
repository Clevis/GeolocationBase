<?php
//setup PHP UTF-8 stuff
setlocale(LC_CTYPE, 'en_US.UTF-8');
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

require_once 'Nette/loader.php';
require_once(__DIR__ . '/Geolocation/exceptions.php');
require_once(__DIR__ . '/Geolocation/interfaces/IGeocodingService.php');
require_once(__DIR__ . '/Geolocation/Address.php');
require_once(__DIR__ . '/Geolocation/Position.php');
require_once(__DIR__ . '/Geolocation/Nominatim/GeocodingClient.php');
require_once(__DIR__ . '/Geolocation/Nominatim/GeocodingResponse.php');
require_once(__DIR__ . '/Geolocation/Google/GeocodingClient.php');
require_once(__DIR__ . '/Geolocation/Google/GeocodingResponse.php');


$geocoderOSM = new \Clevis\Geolocation\Nominatim\GeocodingClient();
$geocoderGoogle = new \Clevis\Geolocation\Google\GeocodingClient();

$address= 'Vítězné náměstí 1500, Praha';

$resultOSM = $geocoderOSM->getPositionAndAddress($address);
$resultGoogle = $geocoderGoogle->getPositionAndAddress($address);

echo $resultOSM[1]->formatedAddress , "\n";
echo $resultGoogle[1]->formatedAddress , "\n";

$position =  \Clevis\Geolocation\Position::fromString("50°1'57.8\"N 15°46'56.21\"E");
var_dump($position);

$resultOSM = $geocoderOSM->getPositionAndAddress($position);
var_dump($resultOSM);
$resultGoogle = $geocoderGoogle->getPositionAndAddress($position);

var_dump($resultGoogle);


