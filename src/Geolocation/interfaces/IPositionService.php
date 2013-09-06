<?php

namespace Clevis\Geolocation;


/**
 * Gets Position from Address. Oposit of IAddressService
 */
interface IPositionService
{

	/**
	 * Get GPS position for given address
	 *
	 * @param Address|string
	 * @param array
	 * @return Position|NULL
	 */
	function getPosition($address, $options = array());

}
