<?php namespace RobStiles\EdiPlug;


use Carbon\Carbon;

/**
 * Class EdiPlug
 *
 * Configures EdiMax EdiPlugs Smart Plugs.
 *
 * @package RobStiles\EdiPlug
 */
class EdiPlug {
	private $host;
	private $curl;
	private $user;
	private $pass;

	/**
	 * @param $host string  Hostname or IP address of the plug
	 * @param $user string User name
	 * @param $pass string Password
	 */
	function __construct($host, $user, $pass)
	{
		$this->host = $host;
		$this->user = $user;
		$this->pass = $pass;

		$this->curl = curl_init(sprintf('http://%s:10000/smartplug.cgi', $host));
	}

	/**
	 * Destructor
	 */
	function __destruct()
	{
		if ($this->curl)
			curl_close($this->curl);
	}


	/**
	 * Creates a basic XML document with the common elements pre-created.
	 * @param null $command_id
	 * @return \DOMDocument
	 */
	private function baseXml($command_id = null)
	{
		$xml = new \DOMDocument('1.0', 'UTF8');

		$smartplug = $xml->createElement('SMARTPLUG');
		$smartplug->setAttribute('id', 'edimax');

		$cmd = $xml->createElement('CMD');
		$cmd->setAttribute('id', isset($command_id) ? $command_id : '');

		$smartplug->appendChild($cmd);
		$xml->appendChild($smartplug);

		return $xml;
	}


	/**
	 * Set the current power state of the plug
	 *
	 * @param bool $on
	 * @throws \Exception
	 */
	private function setPower($on = true)
	{
		$xml = $this->baseXml('setup');

		$cmd = $xml->getElementsByTagName('CMD')->item(0);

		$power = $xml->createElement('Device.System.Power.State');
		$power->textContent = $on ? 'ON' : 'OFF';
		$cmd->appendChild($power);

		$xml = $this->send($xml);
		$this->checkSetupResponse($xml);
	}


	/**
	 * Gets the current power state of the plug.
	 *
	 * @return bool
	 * @throws \Exception
	 */
	private function getPower()
	{
		$xml = $this->baseXml('get');

		$cmd = $xml->getElementsByTagName('CMD')->item(0);

		$power = $xml->createElement('Device.System.Power.State');
		$cmd->appendChild($power);

		$xml = $this->send($xml);
		$state_node = $xml->getElementsByTagName('Device.System.Power.State')->item(0);

		if (!$state_node) throw new \Exception('Expected element not found in response from EdiPlug.');
		return $state_node->nodeValue == 'ON';


	}

	/**
	 * Sends the provided XML to the plug and returns its response.
	 *
	 * @param \DOMDocument $xml
	 * @return \DOMDocument
	 * @throws \Exception
	 */
	private function send(\DOMDocument $xml)
	{
		curl_setopt_array($this->curl, [
			CURLOPT_USERPWD => $this->user . ':' . $this->pass,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => $xml->saveXML(),
			CURLOPT_HTTPHEADER => [
				'Expect: ',
			]
		]);

		$response = curl_exec($this->curl);
		if (! $response) throw new \Exception('Unable to communicate with EdiPlug.');

		$xml = new \DOMDocument();
		$xml->loadXML($response);
		$cmds = $xml->getElementsByTagName('CMD');
		if (! count($cmds)) throw new \Exception('Expected element not found in response from EdiPlug.');
		return $xml;
	}

	/**
	 * Generates XML command to retrieve schedule information.
	 * @return \DOMDocument
	 */
	private function getScheduleXml()
	{
		$xml = $this->baseXml('get');
		$cmd = $xml->getElementsByTagName('CMD')->item(0);

		$schedule_node = $xml->createElement('SCHEDULE');
		$cmd->appendChild($schedule_node);
		return $xml;
	}

	/**
	 * Turns EdiPlug on.
	 */
	public function on()
	{
		$this->setPower(true);
	}

	/**
	 * Turns EdiPlug off.
	 */
	public function off()
	{
		$this->setPower(false);
	}

	/**
	 * Retrieve on / off schedule
	 *
	 * @return WeekSchedule
	 * @throws \Exception
	 */
	public function getSchedule()
	{
		$xml = $this->getScheduleXml();
		$response = $this->send($xml);

		$schedule = new WeekSchedule();
		for ($day = 0; $day < 7; $day++) {
			$schedule_node = $response->getElementsByTagName('Device.System.Power.Schedule.' . $day)->item(0);

			$schedule->setDayFromHexString(
					$day,
					$schedule_node->nodeValue,
					$schedule_node->getAttribute('value') == 'ON'
			);
		}
		return $schedule;
	}

	/**
	 * Checks that the last command sent to the plug was successful.
	 *
	 * @param $xml
	 * @throws \Exception
	 */
	private function checkSetupResponse($xml)
	{
		$cmd = $xml->getElementsByTagName('CMD')->item(0);
		if (($cmd) && ($cmd->nodeValue != 'OK')) throw new \Exception('EdiPlug reports that the command failed.');
	}

	/**
	 * Sets a new schedule.
	 *
	 * @param WeekSchedule $schedule
	 * @throws \Exception
	 */
	public function setSchedule(WeekSchedule $schedule)
	{
		$xml = $this->baseXml('setup');
		$cmd = $xml->getElementsByTagName('CMD')->item(0);

		$schedule_node = $xml->createElement('SCHEDULE');
		$cmd->appendChild($schedule_node);

		for ($day = 0; $day < 7; $day++) {
			$day_schedule = $schedule->day($day);

			$day_node = $xml->createElement('Device.System.Power.Schedule.' . $day);
			$day_node->setAttribute('value', $day_schedule->enabled ? 'ON' : 'OFF');
			$day_node->nodeValue = $day_schedule->getHexString();

			$schedule_node->appendChild($day_node);
		}

		$xml = $this->send($xml);
		$this->checkSetupResponse($xml);
	}

	/**
	 * Magic property getter.
	 *
	 * @param $name
	 * @return WeekSchedule|bool
	 * @throws \Exception
	 */
	public function __get($name)
	{
		switch($name) {
			case 'power':
				return $this->getPower();

			case 'schedule':
				return $this->getSchedule();

		}
	}

	/**
	 * Magic property setter.
	 *
	 * @param $name
	 * @param $val
	 */
	public function __set($name, $val)
	{
		switch($name) {
			case 'power':
				$this->setPower($val);
				break;
			case 'schedule':
				$this->setSchedule($val);
				break;
		}
	}

}