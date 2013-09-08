<?php

namespace Clevis\Geolocation;

use Nette\Object;


/**
 * Converts addresses to positions and elevation and back
 *
 * @property Rectangle $bounds
 */
class Geocoder extends Object
{

	const
		OPTION_SENSOR = 'sensor',
		OPTION_LANGUAGE = 'language',
		OPTION_REGION = 'region',
		OPTION_BOUNDS = 'bounds',
		OPTION_FILTERS = 'filters';

	/** @var IGeocodingService */
	private $geocodingService;

	/** @var IGeocodingCache */
	private $geocodingCache;

	/** @var bool whether the location comes from a device with loc. sensor */
	public $sensor = FALSE;

	/** @var string "cs" language of results */
	public $language;

	/** @var string "cz" prefered region for search (domain) */
	public $region;

	/** @var Rectangle */
	private $bounds;

	/** @var array eg. array('country' => 'cz') */
	private $filters;


	public function __construct(IGeocodingService $geocodingService)
	{
		$this->geocodingService = $geocodingService;
	}

	public function setGeocodingCache(IGeocodingCache $geocodingCache)
	{
		$this->geocodingCache = $geocodingCache;
	}

	public function setBounds(Rectangle $bounds)
	{
		$this->bounds = $bounds;
	}

	public function getBounds()
	{
		return $this->bounds;
	}

	/**
	 * Tells the GPS position for given Address
	 *
	 * @param string|Address
	 * @return Position|NULL
	 * @throws GeocodingException
	 */
	public function getPositionFromAddress($query)
	{
		$options = $this->getOptions();

		if ($this->geocodingCache)
		{
			$position = $this->geocodingCache->getPosition($query, $options);
			if ($position)
			{
				return $position;
			}
		}

		list($position, $address) = $this->geocodingService->getPositionAndAddress($query, $options);
		if ($position && $this->geocodingCache)
		{
			$this->geocodingCache->saveResult($position, $address, $query, $options);
		}

		return $position;
	}

	/**
	 * Finds and Address to given GPS position
	 *
	 * @param Position
	 * @return Address|NULL
	 * @throws GeocodingException
	 */
	public function getAddressFromPosition(Position $query)
	{
		$options = $this->getOptions();

		if ($this->geocodingCache)
		{
			$result = $this->geocodingCache->getAddress($query, $options);
			if ($result)
			{
				return $result;
			}
		}

		list($position, $address) = $this->geocodingService->getPositionAndAddress($query, $options);
		if ($address && $this->geocodingCache)
		{
			$this->geocodingCache->saveResult($position, $address, $query, $options);
		}

		return $address;
	}

	/**
	 * Returns all options
	 *
	 * @return array
	 */
	public function getOptions()
	{
		$options = array();
		if (isset($this->sensor))   $options['sensor']   = (bool) $this->sensor;
		if (isset($this->language)) $options['language'] = $this->language;
		if (isset($this->region))   $options['region']   = $this->region;
		if (isset($this->bounds))   $options['bounds']   = $this->bounds;
		if (isset($this->filters))  $options['filters']  = $this->filters;

		return $options;
	}

}
