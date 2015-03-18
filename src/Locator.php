<?php
/**
 * Created by PhpStorm.
 * User: robs
 * Date: 18/03/15
 * Time: 15:27
 */

namespace RobStiles\EdiPlug;


class Locator {
	private $socket;
	private $broadcast_packet;
	private $plugs = [];

	function __construct()
	{
		$this->broadcast_packet = pack('C6a13C3', 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 'EDIMAX', 0xA1, 0xFF, 0x5E);
	}


	public function scan($seconds)
	{
		$start_time = time();
		$this->plugs = [];

		$this->socket =  socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
		if (false === $this->socket)
			throw new \Exception('Unable to create socket.');

		if ( ! socket_set_nonblock($this->socket))
			throw new \Exception('Unable to set non-blocking option on socket.');

		if ( !socket_set_option($this->socket, SOL_SOCKET, SO_BROADCAST, 1))
			throw new \Exception('Unable to set broadcast option on socket.');

		while(time() < $start_time+$seconds) {
			$this->broadcast();
			$reply = socket_read($this->socket, 186);
			if (false === $reply) {
				$socket_error = socket_last_error($this->socket);
				if ($socket_error != 35) { //EAGAIN
					throw new \Exception(sprintf("Socket error %d: %s", $socket_error, socket_strerror($socket_error)));
				}
			}
			if ($reply)
				$this->handleReply($reply);

			usleep(500000);
		}
		$plugs = [];
		foreach($this->plugs as $plug) {
			$plugs[] = $plug;
		}

		return $plugs;

	}

	private function handleReply($reply)
	{
		$data = unpack('C6MAC/a12manu/Lunknown/a14model/a136version/Sunknown/C4ip/L2unknown', $reply);

		$mac = sprintf('%x:%x:%x:%x:%x:%x', $data['MAC1'], $data['MAC2'], $data['MAC3'], $data['MAC4'], $data['MAC5'], $data['MAC6']);
		if (! isset($this->plugs[$mac])) {
			$manufacturer = rtrim($data['manu']);
			$model = rtrim($data['model']);
			$version = rtrim($data['version']);
			$ip_address = sprintf('%d.%d.%d.%d', $data['ip1'], $data['ip2'], $data['ip3'], $data['ip4']);

			$this->plugs[$mac] = new PlugInfo($mac, $manufacturer, $model, $version, $ip_address);
		}
	}

	private function broadcast()
	{

		$bytes_sent = socket_sendto($this->socket, $this->broadcast_packet, strlen($this->broadcast_packet), 0, '255.255.255.255', 20560);
		if ($bytes_sent == false) {
			$socket_error = socket_last_error($this->socket);
			throw new \Exception(sprintf("Socket error %d: %s", $socket_error, socket_strerror($socket_error)));
		}
	}
}