<?php namespace RobStiles\EdiPlug;


use Carbon\Carbon;

/**
 * Class DaySchedule
 *
 * Stores the TimePeriods for the day.
 *
 * @package RobStiles\EdiPlug
 */
class DaySchedule implements \ArrayAccess, \Iterator, \Countable {
	public $enabled;
	private $periods;
	private $current_index = 0;

	/**
	 * Constructor
	 */
	function __construct()
	{
		$this->enabled = true;
		$this->periods = [];
	}

	/**
	 * Static function to create a new instance of the object which is initialized
	 * from the supplied hexadecimal string.
	 *
	 * @param string $string The hexadecimal string containing the encoded schedules.
	 * @param bool $enabled Whether schedule is enabled
	 * @return DaySchedule
	 */
	public static function createFromHexString($string, $enabled = true)
	{
		$obj = new DaySchedule();
		$obj->enabled = $enabled;
		$obj->setFromHexString($string);
		return $obj;
	}

	/**
	 * Initializes TimePeriods from the supplied encoded hexadecimal string.
	 *
	 * @param string $string
	 */
	public function setFromHexString($string)
	{
		$total_minutes = 0;
		$current_period = new TimePeriod();

		for($index =0; $index < 360; $index++) {
			// Each $schedule_char represents 4 minutes
			$minutes = hexdec($string[$index]);

			for ($i=0; $i < 4; $i++) {
				if (($minutes & 8) && (null === $current_period->on)) {
					$current_period->on = Carbon::createFromTime(0, 0, 0)->addMinutes($total_minutes);
				}
				elseif ((0 ==  $minutes) && (null !== $current_period->on)) {
					$current_period->off = Carbon::createFromTime(0, 0, 0)->addMinutes($total_minutes);
					$this->add($current_period);
					$current_period = new TimePeriod();
				}

				$minutes = ($minutes << 1) & 15;;
				$total_minutes++;
			}
		}

		if (isset($current_period->on) && is_null($current_period->off)) {
			$current_period->off = Carbon::createFromTime(23, 59, 59);
			$this->add($current_period);
		}
	}


	/**
	 * Determines if our schedule would have the EdiPlug powered on during the supplied time.
	 *
	 * @param Carbon $current_time
	 * @return bool
	 */
	private function isTimeInSchedule(Carbon $current_time)
	{
		foreach($this->periods as $period) {
			if (($current_time >= $period->on) && ($current_time < $period->off))
				return true;
		}
		return false;

	}

	/**
	 * Returns our TimePeriods in an encoded hexadecimal string.
	 *
	 * @return string
	 */
	public function getHexString()
	{
		$current_time = Carbon::createFromTime(0,0,0);
		$end_of_day = Carbon::now()->endOfDay();
		$schedule_string = '';

		$four_min = '';
		while ($current_time < $end_of_day) {
			$four_min .= $this->isTimeInSchedule($current_time) ? '1' : '0';

			if (strlen($four_min) == 4) {
				$schedule_string .= strtoupper(dechex(bindec($four_min)));
				$four_min = '';
			}

			$current_time->addMinute();
		}
		return $schedule_string;
	}

	/**
	 * Returns the array of TimePeriods.
	 *
	 * @return TimePeriod[]
	 */
	public function getPeriods()
	{
		return $this->periods;
	}

	/**
	 * Add a new TimePeriod.
	 *
	 * @param TimePeriod $period
	 */
	public function add(TimePeriod $period)
	{
		$this->periods[] = $period;
	}


	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Whether a offset exists
	 * @link http://php.net/manual/en/arrayaccess.offsetexists.php
	 * @param mixed $offset <p>
	 * An offset to check for.
	 * </p>
	 * @return boolean true on success or false on failure.
	 * </p>
	 * <p>
	 * The return value will be casted to boolean if non-boolean was returned.
	 */
	public function offsetExists($offset)
	{
		return isset($this->periods[$offset]);
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Offset to retrieve
	 * @link http://php.net/manual/en/arrayaccess.offsetget.php
	 * @param mixed $offset <p>
	 * The offset to retrieve.
	 * </p>
	 * @return mixed Can return all value types.
	 */
	public function offsetGet($offset)
	{
		return $this->periods[$offset];
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Offset to set
	 * @link http://php.net/manual/en/arrayaccess.offsetset.php
	 * @param mixed $offset <p>
	 * The offset to assign the value to.
	 * </p>
	 * @param mixed $value <p>
	 * The value to set.
	 * </p>
	 * @return void
	 */
	public function offsetSet($offset, $value)
	{
		if (is_null($offset))
			$this->periods[] = $value;
		else
			$this->periods[$offset] = $value;
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Offset to unset
	 * @link http://php.net/manual/en/arrayaccess.offsetunset.php
	 * @param mixed $offset <p>
	 * The offset to unset.
	 * </p>
	 * @return void
	 */
	public function offsetUnset($offset)
	{
		unset($this->periods[$offset]);
	}


	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Return the current element
	 * @link http://php.net/manual/en/iterator.current.php
	 * @return mixed Can return any type.
	 */
	public function current()
	{
		return $this->periods[$this->current_index];
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Return the key of the current element
	 * @link http://php.net/manual/en/iterator.key.php
	 * @return mixed scalar on success, or null on failure.
	 */
	public function key()
	{
		return $this->current_index;
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Move forward to next element
	 * @link http://php.net/manual/en/iterator.next.php
	 * @return void Any returned value is ignored.
	 */
	public function next()
	{
		$this->current_index++;
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Rewind the Iterator to the first element
	 * @link http://php.net/manual/en/iterator.rewind.php
	 * @return void Any returned value is ignored.
	 */
	public function rewind()
	{
		$this->current_index = 0;
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Checks if current position is valid
	 * @link http://php.net/manual/en/iterator.valid.php
	 * @return boolean The return value will be casted to boolean and then evaluated.
	 * Returns true on success or false on failure.
	 */
	public function valid()
	{
		return isset($this->periods[$this->current_index]);
	}

	/**
	 * (PHP 5 &gt;= 5.1.0)<br/>
	 * Count elements of an object
	 * @link http://php.net/manual/en/countable.count.php
	 * @return int The custom count as an integer.
	 * </p>
	 * <p>
	 * The return value is cast to an integer.
	 */
	public function count()
	{
		return count($this->periods);
	}


}