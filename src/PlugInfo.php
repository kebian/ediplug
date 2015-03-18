<?php

namespace RobStiles\EdiPlug;


class PlugInfo {
	public $mac;
	public $manufacturer;
	public $model;
	public $version;
	public $ip_address;

	function __construct($mac, $manufacturer, $model, $version, $ip_address)
	{
		$this->mac = $mac;
		$this->manufacturer = $manufacturer;
		$this->model = $model;
		$this->version = $version;
		$this->ip_address = $ip_address;
	}
}