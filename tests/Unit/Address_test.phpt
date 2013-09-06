<?php

require __DIR__ . '/../boot.php';

use Tester\Assert;
use Clevis\Geolocation\Address;

$addr = new Address;
$addr->country = 'Česká Republika';
$addr->countryCode = 'CZ';
$addr->region = 'Hlavní Město Praha';
$addr->district = 'Hlavní Město Praha';
$addr->town = 'Praha';
$addr->quarter = 'Praha 8';
$addr->neighborhood = 'Kobylisy';
$addr->postalCode = '18200';
$addr->street = 'Neratovická';
$addr->number = '21';

$addr->language = 'en';
Assert::same('21 Neratovická, Praha 8, 18200, Česká Republika', $addr->formatedAddress);
Assert::same('21 Neratovická, Praha 8, 18200', $addr->localFormatedAddress);

$addr->setFormatedAddress(NULL);
$addr->language = 'cs';
Assert::same('Neratovická 21, 18200 Praha 8, Česká Republika', $addr->formatedAddress);
Assert::same('Neratovická 21, 18200 Praha 8', $addr->localFormatedAddress);

// neighbourhood
$addr->setFormatedAddress(NULL);
$addr->number = NULL;
$addr->street = NULL;
Assert::same('Kobylisy, 18200 Praha 8, Česká Republika', $addr->formatedAddress);

$addr->setFormatedAddress(NULL);
$addr->postalCode = NULL;
Assert::same('Kobylisy, Praha 8, Česká Republika', $addr->formatedAddress);

$addr->setFormatedAddress(NULL);
$addr->neighborhood = NULL;
Assert::same('Praha 8, Česká Republika', $addr->formatedAddress);

$addr->setFormatedAddress(NULL);
$addr->quarter = NULL;
Assert::same('Praha, Česká Republika', $addr->formatedAddress);

$addr->setFormatedAddress(NULL);
$addr->town = NULL;
Assert::same('Hlavní Město Praha, Česká Republika', $addr->formatedAddress);

$addr->setFormatedAddress(NULL);
$addr->district = NULL;
Assert::same('Hlavní Město Praha, Česká Republika', $addr->formatedAddress);

$addr->setFormatedAddress(NULL);
$addr->region = NULL;
Assert::same('Česká Republika', $addr->formatedAddress);



$addr = new Address;
$addr->country = 'United States';
$addr->countryCode = 'US';
$addr->state = 'California';
$addr->stateCode = 'CA';
$addr->region = 'Santa Clara';
$addr->district = 'Santa Clara';
$addr->town = 'Mountain View';
$addr->postalCode = '94043';
$addr->street = 'Amphitheatre Pkwy';
$addr->number = '1600';

$addr->language = 'cs';
Assert::same('Amphitheatre Pkwy 1600, 94043 Mountain View, CA, United States', $addr->formatedAddress);
Assert::same('Amphitheatre Pkwy 1600, 94043 Mountain View, CA', $addr->localFormatedAddress);

$addr->setFormatedAddress(NULL);
$addr->language = 'en';
Assert::same('1600 Amphitheatre Pkwy, Mountain View, CA 94043, United States', $addr->formatedAddress);
Assert::same('1600 Amphitheatre Pkwy, Mountain View, CA 94043', $addr->localFormatedAddress);

$addr->setFormatedAddress(NULL);
$addr->street = NULL;
$addr->number = NULL;
Assert::same('Mountain View, CA 94043, United States', $addr->formatedAddress);

$addr->setFormatedAddress(NULL);
$addr->town = NULL;
Assert::same('Santa Clara, CA 94043, United States', $addr->formatedAddress);

$addr->setFormatedAddress(NULL);
$addr->postalCode = NULL;
Assert::same('Santa Clara, CA, United States', $addr->formatedAddress);

$addr->setFormatedAddress(NULL);
$addr->district = NULL;
Assert::same('Santa Clara, CA, United States', $addr->formatedAddress);

$addr->setFormatedAddress(NULL);
$addr->region = NULL;
Assert::same('CA, United States', $addr->formatedAddress);

$addr->setFormatedAddress(NULL);
$addr->stateCode = NULL;
Assert::same('California, United States', $addr->formatedAddress);

$addr->setFormatedAddress(NULL);
$addr->state = NULL;
Assert::same('United States', $addr->formatedAddress);
