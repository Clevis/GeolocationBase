<?php

require __DIR__ . '/../vendor/nette/tester/Tester/bootstrap.php';
require __DIR__ . '/../vendor/autoload.php';

require __DIR__ . '/../src/Geolocation/interfaces/IArea.php';
require __DIR__ . '/../src/Geolocation/interfaces/IAddressService.php';
require __DIR__ . '/../src/Geolocation/interfaces/IAddressCache.php';
require __DIR__ . '/../src/Geolocation/interfaces/IElevationService.php';
require __DIR__ . '/../src/Geolocation/interfaces/IElevationCache.php';
require __DIR__ . '/../src/Geolocation/interfaces/IPositionService.php';
require __DIR__ . '/../src/Geolocation/interfaces/IPositionCache.php';

require __DIR__ . '/../src/Geolocation/exceptions.php';

require __DIR__ . '/../src/Geolocation/Position.php';
require __DIR__ . '/../src/Geolocation/Circle.php';
require __DIR__ . '/../src/Geolocation/Rectangle.php';
require __DIR__ . '/../src/Geolocation/Address.php';
require __DIR__ . '/../src/Geolocation/Geocoder.php';

require __DIR__ . '/../src/Geolocation/Google/ElevationClient.php';
require __DIR__ . '/../src/Geolocation/Google/GeocodingClient.php';
require __DIR__ . '/../src/Geolocation/Google/GeocodingResult.php';


Nette\Diagnostics\Debugger::$logDirectory = __DIR__ . '/output';

function d($v)
{
	echo \Tester\Dumper::toPhp($v) . "\n";
}
