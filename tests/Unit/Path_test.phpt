<?php

require __DIR__ . '/../boot.php';

use Tester\Assert;
use Clevis\Geolocation\Path;
use Clevis\Geolocation\Position;


// length from vector
$path = new Path([new Position(0, 0, 1), new Position(0, 1, 0), new Position(1, 0, 0), new Position(0, 0, 1)]);
Assert::same(round(30022630.19403, 2), round($path->calculateLength(), 2));

// length from degrees
// 1 degree of longitude is exactly 100 km on this latitude
$refLat = 25.930961854617808385300934161791;
$points = [
	new Position($refLat, 0.001),
	new Position($refLat, 0.002),
	new Position($refLat, 0.003),
	new Position($refLat, 0.004),
];
$path = new Path($points);
Assert::same(300.0, round($path->calculateLength(), 3));
