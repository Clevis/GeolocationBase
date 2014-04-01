<?php

require __DIR__ . '/../boot.php';

use Clevis\Geolocation\Address;
use Clevis\Geolocation\Position;
use Clevis\Geolocation\Google\GeocodingClient;
use Tester\Assert;

$y = new GeocodingClient();

$address = new Address;
$address->country = 'Česká Republika';
$address->countryCode = 'CZ';
$address->region = 'Hlavní Město Praha';
$address->district = 'Hlavní Město Praha';
$address->town = 'Praha';
$address->quarter = 'Praha 3';
$address->postalCode = '13000';
$address->number = '27';
$address->street = 'Husitska';

$address->language = 'cs';
$position = $y->getPosition($address);

Assert::same(50.0868624, round($position->latitude, 8));
Assert::same(14.4477158, round($position->longitude, 8));
