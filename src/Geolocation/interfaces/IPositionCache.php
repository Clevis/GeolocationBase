<?php

namespace Clevis\Geolocation;


/**
 *
 */
interface IPositionCache extends IPositionService
{

	/**
	 * Get GPS position for given address
	 *
	 * @param Address|string
	 * @param Position
	 * @param array
	 */
	function savePosition($address, Position $position, $options = array());

}
