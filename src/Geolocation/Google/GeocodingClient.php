<?php

namespace Clevis\Geolocation\Google;

use Nette\Object;
use Nette\InvalidArgumentException;
use Clevis\Geolocation\Position;
use Clevis\Geolocation\Rectangle;
use Clevis\Geolocation\Address;
use Clevis\Geolocation\IGeocodingService;
use Clevis\Geolocation\ConnectionException;
use Clevis\Geolocation\InvalidStatusException;
use Clevis\Geolocation\InvalidResponseException;


/**
 * Client for Google Maps Geocoding API
 *
 * "Use of the Google Geocoding API is subject to a query limit of 2,500 requests per day."
 */
class GeocodingClient extends Object implements IGeocodingService
{

	const GOOGLE_URL = 'http://maps.googleapis.com/maps/api/geocode/json?';


	/**
	 * Get GPS position for given address
	 *
	 * @param Address|string
	 * @param array
	 * @param bool
	 * @return Position|NULL
	 */
	public function getPosition($address, $options = array(), $fullResult = FALSE)
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
				$components[] = "$key:$value";
			}
			$options['components'] = implode('|', $components);
			unset($options['filters']);
		}

		$options['sensor'] = FALSE;

		$result = $this->getResponse($address, $options);

		return $fullResult ? $result : $result->getPosition();
	}

	/**
	 * Get address for given GPS position
	 *
	 * @param Position
	 * @param array
	 * @param bool
	 * @return Address|NULL
	 */
	public function getAddress(Position $position, $options = array(), $fullResult = FALSE)
	{
		$result = $this->getResponse($position, $options);

		return $fullResult ? $result : $result->getAddress();
	}

	/**
	 * Get both position and address for given query
	 *
	 * @param string|Address|Position
	 * @param array
	 * @return array (Position|NULL, Address|NULL)
	 */
	public function getPositionAndAddress($query, $options = array())
	{
		if ($query instanceof Position)
		{
			/** @var GeocodingResponse $response */
			$response = $this->getAddress($query, $options, TRUE);
			if ($response)
			{
				return array($response->getPosition(), $response->getAddress());
			}
		}
		else
		{
			/** @var GeocodingResponse $response */
			$response = $this->getPosition($query, $options, TRUE);
			if ($response)
			{
				return array($response->getPosition(), $response->getAddress());
			}
		}

		return array(NULL, NULL);
	}

	/**
	 * Get a full geocoding query result
	 *
	 * @param string|Address|Position
	 * @return GeocodingResponse
	 */
	public function getResponse($query, $options)
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
		if (isset($options['sensor']))
		{
			$options['sensor'] = $options['sensor'] ? 'true' : 'false';
		}
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

		return new GeocodingResponse($this, $payload->results, $options);
	}

}
