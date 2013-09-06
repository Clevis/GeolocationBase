<?php

namespace Clevis\Geolocation;

use Nette\NotImplementedException;
use Nette\Object;


/**
 * Represents a rectangle on map defined by the southwest and northeast latitude/longitude coordinates
 *
 * Do not thing of this as it is an euclidean 2D rectangle, there is nothing like a "rectangle" in spherical geometry.
 * Close to the poles it has a trapezoid shape. When touching the pole itself, it is a triangle.
 */
class Rectangle extends Object implements IArea
{

	/** @var Position south-west corner */
	private $sw;

	/** @var Position north-east corner */
	private $ne;

	/** @var Position */
	private $center;


	public function __construct(Position $southWest, Position $northEast)
	{
		$this->sw = $southWest;
		$this->ne = $northEast;
	}

	/**
	 * @return Position
	 */
	public function getCenter()
	{
		if (!$this->center)
		{
			$x = ($this->sw->x + $this->ne->x) / 2;
			$y = ($this->sw->y + $this->ne->y) / 2;
			$z = ($this->sw->z + $this->ne->z) / 2;
			$this->center = new Position($x, $y, $z, $this->sw->planetRadius);
		}

		return $this->center;
	}

	/**
	 * @return array ($minX, $minY, $minZ, $maxX, $maxY, $maxZ)
	 */
	public function getVectorBounds()
	{
		/*$minZ = $this->sw->z;
		$maxZ = $this->ne->z;

		$bounds = $this->getLatLonBounds(FALSE);
		if ($minZ < 0 && $maxZ > 0)
		{
			// equator
			$par = array(0, $bounds[1], $bounds[3]);
		}
		elseif (abs($minZ) < abs($maxZ))
		{
			// south border
			$par = array($bounds[0], $bounds[1], $bounds[3]);
		}
		else
		{
			// north border
			$par = array($bounds[2], $bounds[1], $bounds[3]);
		}

		$ab = $par[1] - $par[2];
		$ba = $par[2] - $par[1];
		foreach (array(-M_PI_4, 0, M_PI_4, M_PI_2) as $extreme)
		{
			///
		}

		*/

		throw new NotImplementedException;
	}

	/**
	 * Checks if vector angle C is between A and B
	 *
	 * @param Position
	 * @param Position
	 * @param Position
	 * @return bool
	 */
	private function isAngleBetween($a, $b, $c)
	{
		// if (AxB * AxC >= 0 && CxB * CxA >=0)
		return ($a->y * $b->x - $a->x * $b->y) * ($a->y * $c->x - $a->x * $c->y) < 0
			&& ($c->y * $b->x - $c->x * $b->y) * ($c->y * $a->x - $c->x * $a->y) < 0;
	}

	/**
	 * @param bool
	 * @return array ($minLat, $minLon, $maxLat, $maxLon)
	 */
	public function getLatLonBounds($degrees = TRUE)
	{
		if ($degrees)
		{
			return array($this->sw->latitude, $this->sw->longitude, $this->ne->latitude, $this->ne->longitude);
		}
		else
		{
			return array($this->sw->latRad, $this->sw->lonRad, $this->ne->latRad, $this->ne->lonRad);
		}
	}

}
