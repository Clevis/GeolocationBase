<?php

require __DIR__ . '/../boot.php';

use Tester\Assert;
use Clevis\Geolocation\Path;
use Clevis\Geolocation\Position;


// calculateLength
$path = new Path([new Position(0, 0, 1), new Position(0, 1, 0), new Position(1, 0, 0), new Position(0, 0, 1)]);
Assert::same(round(30022.63019403, 5), round($path->calculateLength(), 5));
