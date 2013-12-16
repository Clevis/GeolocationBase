<?php

namespace Clevis\Geolocation;

use Nette\Object;


class Path extends Object
{

	/** @var Position[] */
	protected $points;

	/**
	 * @param Position[]
	 */
	public function __construct($points = [])
	{
		$this->addPoints($points);
	}

	/**
	 * @param Position
	 */
	public function addPoint(Position $point)
	{
		$this->points[] = $point;
	}

	/**
	 * @param Position[]
	 */
	public function addPoints(array $points)
	{
		foreach ($points as $point)
		{
			$this->addPoint($point);
		}
	}

	/**
	 * @return float [m]
	 */
	public function calculateLength()
	{
		$prev = NULL;
		$length = 0;
		/** @var Position $point */
		foreach ($this->points as $point)
		{
			if (!$prev)
			{
				$prev = $point;
				continue;
			}

			$length += $point->calculateDistanceFrom($prev);
			$prev = $point;
		}

		return $length;
	}

	/**
	 * @return Position|NULL
	 */
	public function getStartPoint()
	{
		return !empty($this->points) ? $this->points[0] : NULL;
	}

	/**
	 * @return Position|NULL
	 */
	public function getEndPoint()
	{
		return !empty($this->points) ? end($this->points) : NULL;
	}

	/**
	 * @return Position[]
	 */
	public function getPoints()
	{
		return $this->points;
	}

}
