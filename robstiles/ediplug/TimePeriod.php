<?php namespace RobStiles\EdiPlug;


use Carbon\Carbon;

/**
 * Class TimePeriod
 *
 * Simple class to store an "on" and "off" time.  Although Carbon also stores dates, we only use the
 * time portions.
 *
 * @package RobStiles\EdiPlug
 */
class TimePeriod {
	public $on;
	public $off;

	function __construct(Carbon $on = null, Carbon $off = null)
	{
		$this->on = $on;
		$this->off = $off;
	}

	/**
	 * Static constructor which initialises times from supplied arguments.
	 *
	 * @param Carbon $on
	 * @param Carbon $off
	 * @return TimePeriod
	 */
	public static function createFromTimes(Carbon $on, Carbon $off)
	{
		return new TimePeriod($on, $off);
	}

}