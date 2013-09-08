<?php

require __DIR__ . '/../boot.php';

use Clevis\Geolocation\Position;
use Nette\Utils\Strings;


$file = __DIR__ . '/../../data/PK/pk.txt';

$c = dibi::connect(array(
	'driver'   => 'mysqli',
	'host'     => 'localhost',
	'username' => 'root',
	'password' => '*****',
	'database' => 'geo_benchmark'
));

// database
dibi::query("USE [geo_benchmark]");

$table = 'locations_pk';
dibi::query("TRUNCATE TABLE [$table]");

$f = fopen($file, 'r');
$n = 0;
$rows = array();
while ($r = fgetcsv($f, NULL, "\t"))
{
	// D (3) latitude
	// E (4) longitude
	// Z (25) local name

	$name = Strings::fixEncoding($r[25]);

	// fixEncoding bug?
	if ($name[0] === "\xF0")
	{
		continue;
	}

	try {
		$p = new Position($r[3], $r[4]);
	} catch (\Exception $e) {
		continue;
	}

	$rows[] = array(
		'name' => $name,
		'lat' => $p->latitude,
		'lon' => $p->longitude,
		'latrad' => $p->latRad,
		'lonrad' => $p->lonRad,
		'x' => $p->x,
		'y' => $p->y,
		'z' => $p->z,
	);

	$n++;

	// flush
	if (!($n % 1000))
	{
		try {
			dibi::query("INSERT INTO [$table] %ex", $rows);
		} catch (DibiDriverException $e) {
			// pass
		}
		$rows = array();
	}
}
try {
	dibi::query("INSERT INTO [$table]", $rows);
} catch (DibiDriverException $e) {
	// pass
}

echo $n . " locations loaded";
