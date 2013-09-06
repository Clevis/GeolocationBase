<?php

require __DIR__ . '/../boot.php';

use Tester\Assert;
use Clevis\Geolocation\Position;


// fromString(), setDegrees()
$pos = Position::fromString("50°1'57.8\"N\xC2\xA015°46'56.21\"E");

// degrees
Assert::same(round(50.032722222222, 8), round($pos->latitude, 8));
Assert::same(round(15.782280555555, 8), round($pos->longitude, 8));

// radians
Assert::same(round(0.8732357365096, 8), round($pos->getLatRad(), 8));
Assert::same(round(0.2754527591679, 8), round($pos->getLonRad(), 8));

// lonLatToVector()
// todo: check with independent source data!
Assert::same(round(0.6181347968911, 8), round($pos->x, 8));
Assert::same(round(0.1747080614490, 8), round($pos->y, 8));
Assert::same(round(0.7664114209334, 8), round($pos->z, 8));

// setVector()
$pos = new Position(0.6181347968911, 0.1747080614490, 0.7664114209334);

// vectorToLonLat()
Assert::same(round(50.032722222222, 8), round($pos->latitude, 8));
Assert::same(round(15.782280555555, 8), round($pos->longitude, 8));

// normalize()
$pos = new Position(0.6181347968911 * 5, 0.1747080614490 * 5, 0.7664114209334 * 5);

Assert::same(round(0.6181347968911, 8), round($pos->x, 8));
Assert::same(round(0.1747080614490, 8), round($pos->y, 8));
Assert::same(round(0.7664114209334, 8), round($pos->z, 8));
Assert::same(round(50.032722222222, 8), round($pos->latitude, 8));
Assert::same(round(15.782280555555, 8), round($pos->longitude, 8));

// toString()
Assert::same("50°1'57.8\"N\xC2\xA015°46'56.21\"E", (string) $pos);
