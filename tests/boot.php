<?php

require __DIR__ . '/../vendor/nette/tester/Tester/bootstrap.php';
require __DIR__ . '/../vendor/autoload.php';

require __DIR__ . '/../src/Geolocation/interfaces/IArea.php';
require __DIR__ . '/../src/Geolocation/interfaces/IGeocodingService.php';
require __DIR__ . '/../src/Geolocation/interfaces/IGeocodingCache.php';

require __DIR__ . '/../src/Geolocation/exceptions.php';

require __DIR__ . '/../src/Geolocation/Position.php';
require __DIR__ . '/../src/Geolocation/Circle.php';
require __DIR__ . '/../src/Geolocation/Rectangle.php';
require __DIR__ . '/../src/Geolocation/Address.php';
require __DIR__ . '/../src/Geolocation/Geocoder.php';

require __DIR__ . '/../src/Geolocation/Google/GeocodingClient.php';
require __DIR__ . '/../src/Geolocation/Google/GeocodingResponse.php';


Nette\Diagnostics\Debugger::$logDirectory = __DIR__ . '/output';

function d($v)
{
	echo \Tester\Dumper::toPhp($v) . "\n";
}
