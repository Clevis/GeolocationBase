<?php

namespace Clevis\Geolocation;


/**
 * Gets Position from Address. Oposit of IAddressService
 */
interface IGeocodingService
{

	/**
	 * Get GPS position for given address
	 *
	 * @param Address|string
	 * @param array
	 * @return Position|NULL
	 */
	function getPosition($address, $options = array());

	/**
	 * Get address for given GPS position
	 *
	 * @param Position
	 * @param array
	 * @return Address|NULL
	 */
	function getAddress(Position $position, $options = array());

	/**
	 * Get both address and position for given query
	 *
	 * @param string|Address|Position
	 * @return array (Position|NULL, Address|NULL)
	 */
	function getPositionAndAddress($query);

}
