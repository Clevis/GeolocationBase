<?php

namespace Clevis\Geolocation\Google;

use Clevis\Geolocation\Position;
use Clevis\Geolocation\Rectangle;
use Clevis\Geolocation\Address;
use Nette\Object;
use StdClass;


/**
 * Result received from The Google Geocoding API
 *
 * @author Vojtěch Dobeš
 */
class GeocodingResponse extends Object
{

	static $countriesWithStates = array(
		'US', // United States
		'CA', // Canada
		'MX', // Mexico
		'BR', // Brazil
		'VE', // Venezuela
		'IN', // India
		'AU', // Australia
		'DE', // Germany
		'CH', // Swiss
		'AE', // United Arab Emirates
		'MM', // Myanmar
		// BE?
	);

	/** @var GeocodingClient */
	private $client;

	/** @var StdClass */
	private $result;

	/** @var array */
	private $options;

	/** @var array [# => stdClass] other found locations */
	private $alternatives;

	/** @var array address components */
	private $components = array();


	/**
	 * @param GoogleGeocodingClient
	 * @param StdClass[]
	 * @param array
	 */
	public function __construct(GeocodingClient $client, array $result, $options)
	{
		$this->client = $client;
		$this->result = array_shift($result);
		$this->options = $options;
		$this->alternatives = $result;

		foreach ($this->result->address_components as $component)
		{
			$type = NULL;
			if ($component->types)
			{
				foreach ($component->types as $t)
				{
					if ($t === 'political') continue;
					$type = $t;
				}
			}
			elseif (is_numeric($component->long_name))
			{
				$type = 'house_number';
			}
			$this->components[$type] = $component;
		}
	}

	/**
	 * @return Address
	 */
	public function getAddress()
	{
		$c = $this->components;

		$address = new Address;
		$address->language = isset($this->options['language']) ? $this->options['language'] : NULL;

		$address->country = $this->getCountry();
		$address->countryCode = $this->getCountryCode();

		$offset = 0;
		// state
		if (in_array($address->countryCode, self::$countriesWithStates))
		{
			$offset = 1;
			if (isset($c['administrative_area_level_1']))
			{
				$address->state     = $c['administrative_area_level_1']->long_name;
				$address->stateCode = $c['administrative_area_level_1']->short_name;
				if ($address->state === $address->stateCode)
				{
					$address->stateCode = NULL;
				}
			}
		}

		// region/county
		if (isset($c['administrative_area_level_' . (1 + $offset)]))
		{
			$address->region     = $c['administrative_area_level_' . (1 + $offset)]->long_name;
			$address->regionCode = $c['administrative_area_level_' . (1 + $offset)]->short_name;
			if ($address->region === $address->regionCode)
			{
				$address->regionCode = NULL;
			}
		}

		$address->district = isset($c['administrative_area_level_' . (2 + $offset)])
			? $c['administrative_area_level_' . (2 + $offset)] : NULL;

		$address->town = isset($c['locality']) ? $c['locality'] : NULL;

		// sublocality levels 1-5?
		$address->quarter = isset($c['sublocality']) ? $c['sublocality'] : NULL;

		$address->neighborhood = isset($c['neighborhood']) ? $c['neighborhood'] : NULL;

		// premise, subpremise? (komplex budov, budova)

		$address->postalCode = isset($c['postal_code']) ? $c['postal_code'] : NULL;

		$address->street = isset($c['street_address']) ? $c['street_address']
			: (isset($c['route']) ? $c['route'] : NULL);

		// house and street number (číslo popisné a orientační)
		$n = array();
		if (isset($c['house_number']))
		{
			$n[] = isset($c['house_number']);
		}
		if (isset($c['street_number']))
		{
			$n[] = isset($c['street_number']);
		}
		$n = implode('/', $n);
		$address->number = $n ? $n : NULL;

		// natural_feature, airport, park, point_of_interest
		// postal_town, post_box, establishment, floor, room, parking, train_station

		$address->formatedAddress = $this->getFormatedAddress();

		$address->partialMatch = $this->isPartialMatch();

		return $address;
	}

	/**
	 * Returns formatted label
	 *
	 * @return string
	 */
	public function getFormatedAddress()
	{
		return $this->result->formatted_address;
	}

	/**
	 * Indicates that the response doesn't match exactly the original querys
	 *
	 * @return bool
	 */
	public function isPartialMatch()
	{
		return isset($this->result->partial_match) && $this->result->partial_match;
	}

	/**
	 * Returns GPS position
	 *
	 * @return Position
	 */
	public function getPosition()
	{
		return new Position(
			$this->result->geometry->location->lat,
			$this->result->geometry->location->lng
		);
	}

	/**
	 * Returns rectangle area
	 *
	 * @return Rectangle
	 */
	public function getArea()
	{
		$bounds = isset($this->result->geometry->bounds)
			? $this->result->geometry->bounds
			: $this->result->geometry->viewport;

		return new Rectangle(
			new Position($bounds->southwest->lat, $bounds->southwest->lng),
			new Position($bounds->northeast->lat, $bounds->northeast->lng)
		);
	}

	/**
	 * Returns name of country
	 *
	 * @return string|NULL
	 */
	public function getCountry()
	{
		return isset($this->components['country']) ? $this->components['country']->long_name : NULL;
	}

	/**
	 * Returns short international symbol of country
	 *
	 * @return string
	 */
	public function getCountryCode()
	{
		return isset($this->components['country']) ? $this->components['country']->short_name : NULL;
	}

	/**
	 * Returns TRUE if current Address has any alternatives
	 * (returned by The Google Geocoding API)
	 *
	 * @return bool
	 */
	public function hasAlternatives()
	{
		return (bool) count($this->getAlternatives());
	}

	/**
	 * Returns array of alternative Addresses
	 * (returned by The Google Geocoding API)
	 *
	 * @return array [# => Address]
	 */
	public function getAlternatives()
	{
		$client = $this->client;
		$label = $this->getFormatedAddress();
		$class = get_class($this);
		return array_filter(array_map(function ($alternative) use ($client, $label, $class) {
			if ($alternative->formatted_address === $label
				|| in_array('train_station', $alternative->types)
				|| in_array('postal_town', $alternative->types)
			) {
				return NULL;
			}
			return new $class($client, array($alternative));
		}, $this->alternatives), function ($alternative) { return (bool) $alternative;});
	}

	/**
	 * Returns original response
	 *
	 * @internal
	 * @return stdClass
	 */
	public function getRaw()
	{
		return $this->result;
	}

}
