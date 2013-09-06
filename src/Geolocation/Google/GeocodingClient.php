<?php

namespace Clevis\Geolocation\Google;

use Nette\Object;
use Nette\InvalidArgumentException;
use Clevis\Geolocation\Position;
use Clevis\Geolocation\Rectangle;
use Clevis\Geolocation\Address;
use Clevis\Geolocation\IAddressService;
use Clevis\Geolocation\IPositionService;
use Clevis\Geolocation\ConnectionException;
use Clevis\Geolocation\InvalidStatusException;
use Clevis\Geolocation\InvalidResponseException;


/**
 * Client for Google Maps Geocoding API
 *
 * "Use of the Google Geocoding API is subject to a query limit of 2,500 requests per day."
 */
class GeocodingClient extends Object implements IAddressService, IPositionService
{

	const GOOGLE_URL = 'http://maps.googleapis.com/maps/api/geocode/json?';


	/**
	 * Get GPS position for given address
	 *
	 * @param Address|string
	 * @param array
	 * @return Position|NULL
	 */
	public function getPosition($address, $options = array())
	{
		// address
		if ($address instanceof Address)
		{
			$address = (string) $address;
		}
		elseif (!is_string($address))
		{
			throw new InvalidArgumentException('Address should be instance of Address of a string.');
		}

		// bounds
		if (!empty($options['bounds']) && $options['bounds'] instanceof Rectangle)
		{
			/** @var Rectangle $rec */
			$rec = $options['bounds'];
			$b = $rec->getLatLonBounds();
			$options['bounds'] = "$b[0],$b[1]|$b[2],$b[3]";
		}

		// filters
		if (!empty($options['filters']))
		{
			$components = array();
			foreach ($options['filters'] as $key => $value)
			{
				// todo: translate keys
				$components = "$key:$value";
			}
			$options['components'] = implode('|', $components);
			unset($options['filters']);
		}

		$options['sensor'] = FALSE;

		$result = $this->getResult($address, $options);

		return $result->getPosition();
	}

	/**
	 * Get address for given GPS position
	 *
	 * @param Position
	 * @param array
	 * @return Address|NULL
	 */
	public function getAddress(Position $position, $options = array())
	{
		$result = $this->getResult($position, $options);

		return $result->getAddress();
	}

	/**
	 * Get a full geocoding query result
	 *
	 * @param string|Address|Position
	 * @return GeocodingResult
	 */
	public function getResult($query, $options)
	{
		if ($query instanceof Position)
		{
			$options['latlng'] = "$query->latitude,$query->longitude";
		}
		else
		{
			$options['address'] = (string) $query;
		}

		return $this->query($options);
	}

	/**
	 * Executes query on The Google Geocoding API
	 *
	 * @param  string
	 * @param  string
	 * @param  array [bounds, language, region, sensor]
	 * @return \StdClass
	 */
	private function query(array $options)
	{
		$url = self::GOOGLE_URL . http_build_query($options);

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_URL, $url);
		$response = curl_exec($curl);
		curl_close($curl);

		if (!$response)
		{
			throw new ConnectionException("Unable to connect to geocoding API.");
		}
		$payload = @json_decode($response); // @ - intentionally to escalate error to exception
		if (!$payload)
		{
			throw new InvalidResponseException("Unable to parse response from geocoding API.");
		}
		if ($payload->status != 'OK')
		{
			throw new InvalidStatusException("Geocoding query failed (status: '{$payload->status}').");
		}

		return new GeocodingResult($this, $payload->results, $options);
	}

}
