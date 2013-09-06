<?php

namespace Clevis\Geolocation\Sql;

use Nette\Object;
use Clevis\Geolocation\Position;


/**
 * Creates snippets of SQL code for plain latitude/longitude columns in degrees
 *
 * usage:
 *   $db->query(
 *     "SELECT *, " . $helper->getExpressionForDistance($position) . " AS [distance]
 *     FROM [map]
 * 	   WHERE " . $helper->getConditionForDistance($position, $distance) . "
 *     HAVING [distance] <= %f", $distance);
 */
class LatLonMapperHelper extends Object implements IMapperHelper
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
			$columns = array('latitude', 'longitude');
		}

		/*
		 * Harvesine formula for calculating distance in spherical geometry
		 *
		 * Params: %radius, %lat, %latCol, $lonCol, %lon, %lat, %latCol
		 */
		return sprintf("(%f * ACOS(COS(%f) * COS(RADIANS([%s])) * COS(RADIANS([%s]) - %f) + SIN(%f) * SIN(RADIANS([%s]))))",
			$position->planetRadius, $position->latRad, $columns[0], $columns[1], $position->lonRad, $position->latRad, $columns[0]);
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
			$columns = array('latitude', 'longitude');
		}

		$box = $position->createCirle($distance)->getLatLonBounds();

		return sprintf("([%s] BETWEEN %f AND %f AND [%s] BETWEEN %f AND %f)",
			$columns[0], $box[0], $box[2], $columns[1], $box[1], $box[3]);
	}

}
