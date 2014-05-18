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

	/**
	 * Set the geocoder to bias on a "rectangle" defined by two Position "corners";
	 *  following queries will prefer the viewport set this way.
	 *  Note that there are always two areas likely to be defined this way (see the links);
	 *  choosing the correct one is up to the implementation.
	 * @see http://stackoverflow.com/questions/23084764/draw-rectangle-on-map-given-two-opposite-coordinates-determine-which-ones-ar#comment35283253_23084764
	 * @see http://i.piskvor.org/test/which.png
	 * @see http://i.piskvor.org/test/which2.png
 	 *
	 * @param Position $corner1
	 * @param Position $corner2
	 * @return boolean true if region biasing is available, false otherwise
	 */
	function setBias(Position $corner1, Position $corner2);

	/**
	 * Reset the bias set by setBias; following queries will not have a preferred viewport
	 * @return void
	 */
	function unsetBias();

}
