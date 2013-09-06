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

	/** @var IPositionService */
	private $positionService;

	/** @var IAddressService */
	private $addressService;

	/** @var IPositionCache */
	private $positionCache;

	/** @var IAddressCache */
	private $addressCache;

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


	public function __construct(
		IPositionService $positionService,
		IAddressService $addressService
	) {
		$this->positionService = $positionService;
		$this->addressService = $addressService;
	}

	public function setPositionCache(IPositionCache $positionCache)
	{
		$this->positionCache = $positionCache;
	}

	public function setAddressCache(IAddressCache $addressCache)
	{
		$this->addressCache = $addressCache;
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
	public function getPositionFromAddress($address)
	{
		$options = $this->getOptions();

		if ($this->positionCache)
		{
			$result = $this->positionCache->getPosition($address, $options);
			if ($result)
			{
				return $result;
			}
		}

		$result = $this->positionService->getPosition($address, $options);
		if ($result && $this->positionCache)
		{
			$this->positionCache->savePosition($address, $result, $options);
		}

		return $result;
	}

	/**
	 * Finds and Address to given GPS position
	 *
	 * @param Position
	 * @return Address|NULL
	 * @throws GeocodingException
	 */
	public function getAddressFromPosition(Position $position)
	{
		$options = $this->getOptions();

		if ($this->addressCache)
		{
			$result = $this->addressCache->getAddress($position, $options);
			if ($result)
			{
				return $result;
			}
		}

		$result = $this->addressService->getAddress($position, $options);
		if ($result && $this->addressCache)
		{
			$this->addressCache->saveAddress($position, $result, $options);
		}

		return $result;
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
