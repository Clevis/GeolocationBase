<?php

require __DIR__ . '/../boot.php';

use Clevis\Geolocation\Position;
use Clevis\Geolocation\Sql\LatLonMapperHelper;
use Clevis\Geolocation\Sql\LatLonRadMapperHelper;
use Clevis\Geolocation\Sql\VectorMapperHelper;


$table = 'locations_pk';

$c = dibi::connect(array(
	'driver'   => 'mysqli',
	'host'     => 'localhost',
	'username' => 'root',
	'password' => '*****',
	'database' => 'geo_benchmark'
));

// database
dibi::query("USE [geo_benchmark]");

$lon = 36;
$lat = 72;
$dist = 10;
$llh = new LatLonMapperHelper;
$lrh = new LatLonRadMapperHelper;
$nvh = new VectorMapperHelper;
$llc = array('lat', 'lon');
$lrc = array('latrad', 'lonrad');
$nvc = array('x', 'y', 'z');

$ll = array();
$lr = array();
$nv = array();

for ($n = 0; $n < 60; $n++)
{
	$pos = new Position($lon, $lat);

	if ($n === 0)
	{
		dibi::test(
			"SELECT *, " . $llh->getExpressionForDistance($pos, $llc) . " AS [distance]
			FROM [$table]
			WHERE " . $llh->getConditionForDistance($pos, $dist, $llc) . "
			HAVING [distance] <= %f", $dist
		);

		dibi::test(
			"SELECT *, " . $lrh->getExpressionForDistance($pos, $lrc) . " AS [distance]
			FROM [$table]
			WHERE " . $lrh->getConditionForDistance($pos, $dist, $lrc) . "
			HAVING [distance] <= %f", $dist
		);

		dibi::test(
			"SELECT *, " . $nvh->getExpressionForDistance($pos, $nvc) . " AS [distance]
			FROM [$table]
			WHERE " . $nvh->getConditionForDistance($pos, $dist, $nvc) . "
			HAVING [distance] <= %f", $dist
		);
	}

	/**/$t = microtime(TRUE);
	$res = dibi::query(
		"SELECT *, " . $llh->getExpressionForDistance($pos, $llc) . " AS [distance]
			FROM [$table]
			WHERE " . $llh->getConditionForDistance($pos, $dist, $llc) . "
			HAVING [distance] <= %f", $dist
	);
	$dt = microtime(TRUE) - $t;
	if ($n > 0)
	{
		echo "ll: " . $res->count() . "\n";
		$ll[] = $dt;
	}/**/

	/**/$t = microtime(TRUE);
	$res = dibi::query(
		"SELECT *, " . $lrh->getExpressionForDistance($pos, $lrc) . " AS [distance]
			FROM [$table]
			WHERE " . $lrh->getConditionForDistance($pos, $dist, $lrc) . "
			HAVING [distance] <= %f", $dist
	);
	$dt = microtime(TRUE) - $t;
	if ($n > 0)
	{
		echo "lr: " . $res->count() . "\n";
		$lr[] = $dt;
	}/**/

	/**/$t = microtime(TRUE);
	$res = dibi::query(
		"SELECT *, " . $nvh->getExpressionForDistance($pos, $nvc) . " AS [distance]
			FROM [$table]
			WHERE " . $nvh->getConditionForDistance($pos, $dist, $nvc) . "
			HAVING [distance] <= %f", $dist
	);
	$dt = microtime(TRUE) - $t;
	if ($n > 0)
	{
		echo "nv: " . $res->count() . "\n";
		$nv[] = $dt;
	}/**/

	// prevent query cache
	$lat += 0.01;
}

if ($ll)
{
	$lla = array_sum($ll) / count($ll);
	echo "latitude/longitude: $lla\n";
	d($ll);
}

if ($lr)
{
	$lra = array_sum($lr) / count($lr);
	echo "latrad/lonrad: $lra\n";
	d($lr);
}

if ($nv)
{
	$nva = array_sum($nv) / count($nv);
	echo "normal vector: $nva\n";
	d($nv);
}
