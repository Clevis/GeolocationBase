<?php

namespace Clevis\Geolocation\Sql;

use Nette\Object;
use Clevis\Geolocation\Position;


/**
 * Helper for generating SQL queries for geolocation
 *
 * usage:
 *   $db->query(
 *     "SELECT *, " . $helper->getExpressionForDistance($position) . " AS [distance]
 *     FROM [map]
 * 	   WHERE " . $helper->getConditionForDistance($position, $distance) . "
 *     HAVING [distance] <= %f", $distance);
 */
class VectorMapperHelper extends Object
{

	/**
	 * Returns expression for calculating distance from a given position
	 *
	 * @param Position
	 * @param array - column names (latitude, longitude)
	 * @return string
	 */
	public function getExpressionForDistance(Position $position, array $columns = array())
	{
		if (!$columns)
		{
			$columns = array('location_x', 'location_y', 'location_z');
		}

		RETURN sprintf("(%f * ACOS([%s] * %f + [%s] * %f + [%s] * %f))",
			$position->planetRadius, $columns[0], $position->x, $columns[1], $position->y, $columns[2], $position->z);
	}

	/**
	 * Returns condition for selecting rows by distance
	 *
	 * @param Position
	 * @param float [km]
	 * @param array - column names (latitude, longitude)
	 * @return string
	 */
	public function getConditionForDistance(Position $position, $distance, array $columns = array())
	{
		if (!$columns)
		{
			$columns = array('location_x', 'location_y', 'location_z');
		}

		$box = $position->createCirle($distance)->getVectorBounds();

		return sprintf("([%s] BETWEEN %f AND %f AND [%s] BETWEEN %f AND %f AND [%s] BETWEEN %f AND %f)",
			$columns[0], $box[0], $box[3], $columns[1], $box[1], $box[4], $columns[2], $box[2], $box[5]);
	}

}
