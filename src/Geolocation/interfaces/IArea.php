<?php

namespace Clevis\Geolocation;


interface IArea
{

	/**
	 * @return Position
	 */
	function getCenter();

	/**
	 * @return array ($minX, $minY, $minZ, $maxX, $maxY, $maxZ)
	 */
	function getVectorBounds();

	/**
	 * @param bool - degrees or radians
	 * @return array ($minLat, $minLon, $maxLat, $maxLon)
	 */
	function getLatLonBounds($degrees = TRUE);

}
