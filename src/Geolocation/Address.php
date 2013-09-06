<?php

namespace Clevis\Geolocation;

use Nette\Object;


/**
 * Postal address
 *
 * @property string $formatedAddress
 * @property string $localFormatedAddress
 */
class Address extends Object
{

	/** @var string eg: "en-GB", "cs" */
	public $language;

	/** @var string eg: "United States", "Česká Republika" */
	public $country;

	/** @var string eg: "US", "CZ" */
	public $countryCode;

	/** @var string|NULL eg: "California", NULL */
	public $state;

	/** @var string|NULL eg: "CA", NULL */
	public $stateCode;

	/** @var string eg: "Santa Clara", "Hlavní Město Praha" */
	public $region;

	/** @var string eg: NULL, NULL */
	public $regionCode;

	/** @var string eg: "Santa Clara", "Hlavní Město Praha" */
	public $district;

	/** @var string eg: "Mountain View", "Praha" */
	public $town;

	/** @var string|NULL eg: NULL, "Praha 8" */
	public $quarter;

	/** @var string|NULL eg: NULL, "Kobylisy" */
	public $neighborhood;

	/** @var string|NULL eg: "94043", "18200" */
	public $postalCode;

	/** @var string|NULL eg: "Amphitheatre Pkwy", "Neratovická" */
	public $street;

	/** @var string|NULL eg: "1600", "21" */
	public $number;

	/** @var string */
	private $formatedAddress;

	/** @var bool */
	public $partialMatch;


	/**
	 * @param string
	 */
	public function setFormatedAddress($address)
	{
		$this->formatedAddress = $address;
	}

	/**
	 * Returns formated address including country
	 *
	 * @return string
	 */
	public function getFormatedAddress()
	{
		if (!$this->formatedAddress)
		{
			$this->formatAddress();
		}

		return $this->formatedAddress;
	}

	/**
	 * Returns formated address without country
	 *
	 * @return string
	 */
	public function getLocalFormatedAddress()
	{
		return substr($this->formatedAddress, 0, strrpos($this->formatedAddress, ','));
	}

	/**
	 * Todo: extract to Formater
	 */
	private function formatAddress()
	{
		$address = '';

		// street + number
		$address .= $this->street;
		if ($this->number)
		{
			if ($this->language === 'cs')
			{
				$address .= ' ' . $this->number;
			}
			else
			{
				$address = $this->number . ' ' . $address;
			}
		}

		// use "Praha 1" instead of "Praha 1, Praha"
		if (substr($this->quarter, 0, strlen($this->town)) === $this->town)
		{
			$useQuarter = TRUE;
		}

		if (!$address)
		{
			// [neighborhood]
			$address .= $this->neighborhood;

			// [quarter]
			if (!isset($useQuarter))
			{
				$address .= $this->quarter;
			}
		}

		// town [+ zip]
		if ($address)
		{
			$address .= ', ';
		}
		if ($this->language === 'cs' && $this->postalCode)
		{
			$address .= $this->postalCode . ' ';
		}
		$address .= isset($useQuarter) ? $this->quarter : $this->town;

		// [district]
		if (!$address)
		{
			$address .= $this->district;
		}

		// [region]
		if (!$address)
		{
			$address .= $this->region;
		}

		// state
		if ($address && ($this->state || $this->stateCode))
		{
			$address .= ', ';
		}
		$address .= $this->stateCode ?: $this->state;

		// [zip]
		if ($address && $this->language !== 'cs' && $this->postalCode)
		{
			if (!$this->state && !$this->stateCode)
			{
				$address .= ',';
			}
			$address .= ' ' . $this->postalCode;
		}

		// country
		if ($address)
		{
			$address .= ', ';
		}
		$address .= $this->country;

		$this->formatedAddress = $address;
	}

}
