<?php

namespace RobStiles\EdiPlug;


class PlugInfo {
	private $mac;
	private $manufacturer;
	private $model;
	private $version;
	private $ip_address;

	function __construct($mac, $manufacturer, $model, $version, $ip_address)
	{
		$this->mac = $mac;
		$this->manufacturer = $manufacturer;
		$this->model = $model;
		$this->version = $version;
		$this->ip_address = $ip_address;
	}

	/**
	 * @return mixed
	 */
	public function getIpAddress()
	{
		return $this->ip_address;
	}

	/**
	 * @param mixed $ip_address
	 */
	public function setIpAddress($ip_address)
	{
		$this->ip_address = $ip_address;
	}

	/**
	 * @return mixed
	 */
	public function getMac()
	{
		return $this->mac;
	}

	/**
	 * @param mixed $mac
	 */
	public function setMac($mac)
	{
		$this->mac = $mac;
	}

	/**
	 * @return mixed
	 */
	public function getManufacturer()
	{
		return $this->manufacturer;
	}

	/**
	 * @param mixed $manufacturer
	 */
	public function setManufacturer($manufacturer)
	{
		$this->manufacturer = $manufacturer;
	}

	/**
	 * @return mixed
	 */
	public function getModel()
	{
		return $this->model;
	}

	/**
	 * @param mixed $model
	 */
	public function setModel($model)
	{
		$this->model = $model;
	}

	/**
	 * @return mixed
	 */
	public function getVersion()
	{
		return $this->version;
	}

	/**
	 * @param mixed $version
	 */
	public function setVersion($version)
	{
		$this->version = $version;
	}



}