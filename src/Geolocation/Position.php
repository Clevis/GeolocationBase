<?php

namespace Clevis\Geolocation;

use Nette\Object;
use Nette\Utils\Strings;
use Nette\InvalidArgumentException;


/**
 * GPS position
 *
 * - position can be represented either by latitude and longitude or by a normal vector
 * - calculations are done allways on the normal vector form to prevent pole singularities and 180 meridian problem
 *
 * @property-read float $x
 * @property-read float $y
 * @property-read float $z
 *
 * @property-read float $latitude
 * @property-read float $longitude
 * @property-read float $latRad
 * @property-read float $lonRad
 *
 * @property-read float $planetRadius
 */
class Position extends Object
{

	/** @var float Earth radius in km */
	private $planetRadius = 6371.0;

	/** @var float latitude in radians */
	private $latitude;

	/** @var float longitude in radians */
	private $longitude;

	/** @var float normal-vector components */
	private $x;
	private $y;
	private $z;


	/**
	 * @param float $y/$latitude X part of normal vector or latitude
	 * @param float $x/$longitude Y part of normal vector or longitude
	 * @param float $z/$degrees Z part of normal vector or TRUE for degrees or FALSE for radians
	 * @param float
	 */
	public function __construct($x, $y, $z = TRUE, $planetRadius = NULL)
	{
		if (is_bool($z))
		{
			if ($z)
			{
				$this->setDegrees($x, $y);
			}
			else
			{
				$this->setRadians($x, $y);
			}
		}
		else
		{
			$this->setVector($x, $y, $z);
		}

		if ($planetRadius !== NULL)
		{
			$this->planetRadius = $planetRadius;
		}
	}

	/**
	 * Creates coordinates from string such as 50°1'57.8"N 15°46'56.21"E
	 *
	 * @todo choose format
	 *
	 * @param string
	 * @return self
	 */
	public static function fromString($string)
	{
		@list($n, $e) = preg_split('/[\\s]+/u', $string);
		@list(, $n1, $n2, $n3, $n4) = $m1 = Strings::match($n, "/(\\d+)°(\\d+)'(\\d+(?:\\.\\d+)?)\"(N|S)/");
		@list(, $e1, $e2, $e3, $e4) = $m2 = Strings::match($e, "/(\\d+)°(\\d+)'(\\d+(?:\\.\\d+)?)\"(E|W)/");
		if (!$m1 || !$m2)
		{
			throw new InvalidArgumentException('Invalid coordinates: ' . $string);
		}

		$latitude = (int) $n1 + ((int) $n2 / 60) + ((float) $n3 / 3600);
		$longitude = (int) $e1 + ((int) $e2 / 60) + ((float) $e3 / 3600);

		if ($n4 === 'S') $latitude *= -1;
		if ($e4 === 'W') $longitude *= -1;

		return new self($latitude, $longitude, TRUE);
	}

	/**
	 * Returns a circle centered at this position
	 *
	 * @param float
	 * @return Circle
	 */
	public function createCirle($radius)
	{
		return new Circle($this, $radius);
	}

	/**
	 * @param Position
	 * @return float [km]
	 */
	public function calculateDistanceFrom(Position $position)
	{
		///
	}


	# getters ----------------------------------------------------------------------------------------------------------


	/**
	 * Prints in this format 50°1'57.8"N 15°46'56.21"E
	 *
	 * @return string
	 */
	public function __toString()
	{
		if ($this->latitude === NULL)
		{
			$this->vectorToLonLat();
		}

		$ns = $this->latitude < 0 ? 'S' : 'N';
		$ew = $this->longitude < 0 ? 'W' : 'E';
		$lat = rad2deg(abs($this->latitude));
		$lon = rad2deg(abs($this->longitude));

		$latD = (int) $lat;
		$latM = (int) $latR = (($lat - $latD) * 60);
		$latS = round(($latR - $latM) * 60, 2);

		$lonD = (int) $lon;
		$lonM = (int) $lonR = (($lon - $lonD) * 60);
		$lonS = round(($lonR - $lonM) * 60, 2);

		$nbsp = "\xC2\xA0";

		return "{$latD}°{$latM}'{$latS}\"$ns{$nbsp}{$lonD}°{$lonM}'{$lonS}\"$ew";
	}


	public function getLatitude()
	{
		if ($this->latitude === NULL)
		{
			$this->vectorToLonLat();
		}
		return rad2deg($this->latitude);
	}

	public function getLongitude()
	{
		if ($this->longitude === NULL)
		{
			$this->vectorToLonLat();
		}
		return rad2deg($this->longitude);
	}

	public function getLatRad()
	{
		if ($this->latitude === NULL)
		{
			$this->vectorToLonLat();
		}
		return $this->latitude;
	}

	public function getLonRad()
	{
		if ($this->longitude === NULL)
		{
			$this->vectorToLonLat();
		}
		return $this->longitude;
	}

	public function getVector()
	{
		if ($this->x === NULL)
		{
			$this->lonLatToVector();
		}
		return array($this->x, $this->y, $this->z);
	}

	public function getX()
	{
		if ($this->x === NULL)
		{
			$this->lonLatToVector();
		}
		return $this->x;
	}

	public function getY()
	{
		if ($this->y === NULL)
		{
			$this->lonLatToVector();
		}
		return $this->y;
	}

	public function getZ()
	{
		if ($this->z === NULL)
		{
			$this->lonLatToVector();
		}
		return $this->z;
	}

	public function getPlanetRadius()
	{
		return $this->planetRadius;
	}


	# internals --------------------------------------------------------------------------------------------------------


	private function setDegrees($lat, $lon)
	{
		if (!is_numeric($lat) || $lat > 90 || $lon < -90)
		{
			throw new InvalidArgumentException("Invalid latitude given: '$lat'. Must be a number between -90 and 90.");
		}
		if (!is_numeric($lon) || $lon > 180 || $lon < -180)
		{
			throw new InvalidArgumentException("Invalid longitude given: '$lon'. Must be a number between -180 and 180.");
		}

		$this->latitude = deg2rad($lat);
		$this->longitude = deg2rad($lon);
	}

	private function setRadians($lat, $lon)
	{
		if (!is_numeric($lat) || $lat > M_PI_2 || $lon < -M_PI_2)
		{
			throw new InvalidArgumentException("Invalid latitude given: '$lat'. Must be a number between -90 and 90.");
		}
		if (!is_numeric($lon) || $lon > M_PI || $lon < -M_PI)
		{
			throw new InvalidArgumentException("Invalid longitude given: '$lon'. Must be a number between -180 and 180.");
		}

		$this->latitude = $lat;
		$this->longitude = $lon;
	}

	private function setVector($x, $y, $z)
	{

		if (!is_numeric($x) || !is_numeric($y) || !is_numeric($z))
		{
			throw new InvalidArgumentException("Invalid vector given: ($x, $y, $z).");
		}

		$this->normalize($x, $y, $z);

		$this->x = $x;
		$this->y = $y;
		$this->z = $z;
	}

	/**
	 * Normalize vector
	 *
	 * @param float
	 * @param float
	 * @param float
	 */
	private function normalize(&$x, &$y, &$z)
	{
		$size = abs(sqrt($x*$x + $y*$y + $z*$z));

		if ($size != 1)
		{
			$x = $x / $size;
			$y = $y / $size;
			$z = $z / $size;
		}
	}

	/**
	 * Calculates normal vector from latitude and longitude
	 */
	private function lonLatToVector()
	{
		$this->x = cos($this->latitude) * cos($this->longitude);
		$this->y = cos($this->latitude) * sin($this->longitude);
		$this->z = sin($this->latitude);
	}

	/**
	 * Calculates latitude and longitude from normal vector
	 */
	private function vectorToLonLat()
	{
		$this->longitude = atan2($this->y, $this->x);
		$hyp = sqrt($this->x * $this->x + $this->y * $this->y);
		$this->latitude = atan2($this->z, $hyp);
	}

}
