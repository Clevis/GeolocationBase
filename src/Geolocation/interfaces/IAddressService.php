<?php

namespace Clevis\Geolocation;


/**
 * Gets Address from Position. Oposit of IPositionService
 */
interface IAddressService
{

	/**
	 * Get address for given GPS position
	 *
	 * @param Position
	 * @param array
	 * @return Address|NULL
	 */
	function getAddress(Position $position, $options = array());

}
