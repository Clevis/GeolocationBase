<?php

namespace Clevis\Geolocation;


/**
 *
 */
interface IGeocodingCache extends IGeocodingService
{

	/**
	 * Saves geocoding request result
	 *
	 * @param Position
	 * @param Address
	 * @param string|Address|Position
	 * @param array
	 */
	function saveResult(Position $position, Address $address, $query, $options = array());

}
