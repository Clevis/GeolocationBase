<?php

namespace Clevis\Geolocation;

use Nette\InvalidArgumentException;
use Nette\Object;


class Circle extends Object implements IArea
{

	/** @var Position */
	private $center;

	/** @var float */
	private $radius;


	/**
	 * @param Position
	 * @param float - radius in km
	 */
	public function __construct(Position $center, $radius)
	{
		if (!is_numeric($radius) || $radius <= 0)
		{
			throw new InvalidArgumentException("Invalid circle radius: '$radius'.");
		}
		if ($radius > $center->planetRadius)
		{
			throw new InvalidArgumentException("Circle radius cannot be greater than Earth radius. '$radius' given.");
		}
		$this->center = $center;
		$this->radius = $radius;
	}

	/**
	 * @return Position
	 */
	public function getCenter()
	{
		return $this->center;
	}

	/**
	 * Calculates bounding box for vector positions
	 *
	 * The "box" may be a square, hexagon or something between, depending on the actual location
	 *
	 * @return array ($minX, $minY, $minZ, $maxX, $maxY, $maxZ)
	 */
	public function getVectorBounds()
	{
		$center = $this->center;
		$offset = $this->radius / $center->planetRadius;
		return array(
			$center->x - $offset, $center->y - $offset, $center->z - $offset,
			$center->x + $offset, $center->y + $offset, $center->z + $offset);
	}

	/**
	 * Calculates aproximated bounding box for latitude/longitude positions
	 * @see http://janmatuschek.de/LatitudeLongitudeBoundingCoordinates
	 *
	 * @return array ($minLat, $minLon, $maxLat, $maxLon)
	 */
	public function getLatLonBounds($degrees = TRUE)
	{
		$center = $this->center; // stfu!

		$r = $this->radius / $center->planetRadius;
		$lat = deg2rad($this->center->latitude);
		$lon = deg2rad($this->center->longitude);

		$latT = asin(sin($lat) / cos($r));
		$dLon = acos( ( cos($r) - sin($latT) * sin($lat) ) / ( cos($latT) * cos($lat) ));

		$minLat = $lat - $r;
		$maxLat = $lat + $r;
		$minLon = $lon - $dLon;
		$maxLon = $lon + $dLon;

		if ($degrees)
		{
			return array(rad2deg($minLat), rad2deg($minLon), rad2deg($maxLat), rad2deg($maxLon));
		}
		else
		{
			return array($minLat, $minLon, $maxLat, $maxLon);
		}
	}

}
