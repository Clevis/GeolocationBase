<?php

namespace Clevis\Geolocation;


/**
 *
 */
interface IAddressCache extends IAddressService
{

	/**
	 * Saves address for given GPS position
	 *
	 * @param Position
	 * @param Address
	 * @param array
	 */
	function saveAddress(Position $position, Address $address, $options = array());

}
