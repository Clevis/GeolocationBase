<?php

namespace Clevis\Geolocation\Sql;

use Clevis\Geolocation\Position;


interface IMapperHelper
{

	/**
	 * Returns expression for calculating distance from a given position
	 *
	 * @param Position
	 * @param array - column names (latitude, longitude) or (x, y, z)
	 * @return string
	 */
	function getExpressionForDistance(Position $position, array $columns);

	/**
	 * Returns condition for selecting rows by distance
	 *
	 * @param Position
	 * @param float [km]
	 * @param array - column names (latitude, longitude) or (x, y, z)
	 * @return string
	 */
	public function getConditionForDistance(Position $position, $distance, array $columns = array());

}
