<?php

namespace Clevis\Geolocation\Nominatim;

use Clevis\Geolocation\Position;
use Clevis\Geolocation\Rectangle;
use Clevis\Geolocation\Address;
use Nette\Object;
use StdClass;


/**
 * Result received from OSM Nominatim API
 *
 * @author Jan Martinec
 */
class GeocodingResponse extends Object
{

	static $countriesWithStates = array(
		'US', // United States of America
		'CA', // Canada
		'MX', // Mexico
		'BR', // Brazil
		'VE', // Venezuela
		'IN', // India
		'AU', // Australia
		'DE', // Germany
		'CH', // Switzerland
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

		foreach ($this->result->address as $type => $component)
		{
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

		// state
		if (in_array($address->countryCode, self::$countriesWithStates))
		{
			if (isset($c['state']))
			{
				$address->state     = $c['state'];
				$address->stateCode = NULL;
			}
		}

		// region/county
		if (isset($c['county']))
		{
			$address->region     = $c['county'];
			$address->regionCode = NULL;
		}

		$address->district = isset($c['state_district'])
			? $c['state_district'] : NULL;

		$address->town = isset($c['city']) ? $c['city'] : NULL;

		$address->quarter = isset($c['suburb']) ? $c['suburb'] : NULL;

		$address->neighborhood = isset($c['neighborhood']) ? $c['neighborhood'] : NULL;

		$address->postalCode = isset($c['postcode']) ? $c['postcode'] : NULL;

		$address->street = isset($c['road']) ? $c['road'] : NULL;

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
		$a = $this->components;
		$prahaCislo = (isset($a['postcode']) && $a['postcode']{0} == '1' ? (' '.$a['postcode']{1}) : '');
		//nominatim občas vraci místo baráků různé pomníky,obchody,apod.. - pak chybí house_number
		$string = @"$a[road]$a[pedestrian] $a[house_number], $a[city]" . $prahaCislo;
		return $string;
		//return $this->result->display_name; // returns ugly addresses in CZ - @TODO: FIXME maybe?
	}

	/**
	 * Indicates that the response doesn't match exactly the original querys
	 *
	 * @return bool
	 */
	public function isPartialMatch()
	{
		return false; // no such thing in OSM (yet?)
	}

	/**
	 * Returns GPS position
	 *
	 * @return Position
	 */
	public function getPosition()
	{
		return new Position(
			$this->result->lat,
			$this->result->lon
		);
	}

	/**
	 * Returns rectangle area
	 *
	 * @return Rectangle
	 */
	public function getArea()
	{
		$bounds = $this->result->boundingbox;

		return new Rectangle(
			new Position($bounds[0], $bounds[2]),
			new Position($bounds[1], $bounds[3])
		);
	}

	/**
	 * Returns name of country
	 *
	 * @return string|NULL
	 */
	public function getCountry()
	{
		return isset($this->components['country']) ? $this->components['country'] : NULL;
	}

	/**
	 * Returns short international symbol of country
	 *
	 * @return string
	 */
	public function getCountryCode()
	{
		return isset($this->components['country_code']) ? $this->components['country_code'] : NULL;
	}

	/**
	 * Returns TRUE if current Address has any alternatives
	 * (returned by OSM Nominatim API)
	 *
	 * @return bool
	 */
	public function hasAlternatives()
	{
		return (bool) count($this->getAlternatives());
	}

	/**
	 * Returns array of alternative Addresses
	 * (returned by OSM Nominatim API)
	 *
	 * @return array [# => Address]
	 */
	public function getAlternatives()
	{
		$client = $this->client;
		$label = $this->getFormatedAddress();
		$class = get_class($this);
		return array_filter(array_map(function ($alternative) use ($client, $label, $class) {
				                       if ($alternative->display_name === $label
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
