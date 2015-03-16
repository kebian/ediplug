<?php namespace RobStiles\EdiPlug;


/**
 * Class WeekSchedule
 *
 * Contains DaySchedules representing each day of the week.
 *
 * @package RobStiles\EdiPlug
 */
class WeekSchedule implements \ArrayAccess, \Iterator, \Countable {
	private $days = [];
	private $current_index = 0;

	/**
	 * Day Constants
	 */
	const SUNDAY	= 0;
	const MONDAY	= 1;
	const TUESDAY	= 2;
	const WEDNESDAY	= 3;
	const THURSDAY	= 4;
	const FRIDAY	= 5;
	const SATURDAY	= 6;


	/**
	 * Constructor
	 *
	 * Can be passed an array of DaySchedule objects, indexed by day
	 * number.
	 *
	 * @param array $days
	 */
	function __construct(array $days = [])
	{
		// Initialize each day
		for ($day=0; $day < 7; $day++) {
			$this->days[$day] = new DaySchedule();
		}

		// Override with any passed
		foreach($days as $day => $day_schedule) {
			$this->days[$day] = $day_schedule;
		}
	}

	/**
	 * Sets the DaySchedule for the specified day.
	 *
	 * @param int $day
	 * @param DaySchedule $schedule
	 */
	public function setDay($day, DaySchedule $schedule) {
		$this->days[$day] = $schedule;
	}

	/**
	 * Gets the DaySchedule for the requested day.
	 *
	 * @param int $day
	 * @return mixed
	 */
	public function day($day) {
		return $this->days[$day];
	}

	/**
	 * Internally, the EdiPlug stores the schedule for the day in a hexadecimal string.
	 * This function can set that string directly in the DaySchedule object.
	 *
	 * @param $day
	 * @param $string
	 * @param bool $enabled
	 */
	public function setDayFromHexString($day, $string, $enabled = true)
	{
		$this->setDay($day, DaySchedule::createFromHexString($string, $enabled));
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
		return isset($this->days[$offset]);
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
		return $this->day($offset);
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
			throw new \Exception('There are only 7 days in a week!');
		else
			$this->setDay($offset, $value);
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
		// ignore
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Return the current element
	 * @link http://php.net/manual/en/iterator.current.php
	 * @return mixed Can return any type.
	 */
	public function current()
	{
		return $this->day($this->current_index);
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
		return isset($this->days[$this->current_index]);
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
		return count($this->days);
	}


}