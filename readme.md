GeolocationBase
===============

Tools for geolocation, geocoding and reverse geocoding.

Read *performance.md* for info on performance and problems.


namespace `Clevis\Geolocation`

#### basics:
 - Position
 - Circle ( *IArea* )
 - Rectangle ( *IArea* )
 - SqlGenerator

#### geocoding:
 - Address
 - Geocoder
 - Google\GeocodingApiClient ( *IGeocodingService*, *IReverseGeocodingService* )
 - Google\ElevationApiClient ( *IElevationService* )

#### tests:
First get a nette tester via `composer update --prefer-dist`. Then run the tests using `vendor/bin/tester tests/` or similar way on Windows.
