<?php

namespace Clevis\Geolocation;


class GeocodingException extends \RuntimeException
{}

class ConnectionException extends GeocodingException
{}

class InvalidResponseException extends GeocodingException
{}

class InvalidStatusException extends GeocodingException
{}
