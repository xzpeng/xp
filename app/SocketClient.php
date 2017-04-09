<?php
namespace App;

class SocketClient
{
	protected $host;
	protected $port;
	protected $socket;
	protected $cmdxml;


	public function __construct($host, $port, $xml_data)
	{
		$this->host = $host;
		$this->port = $port;
		$this->cmdxml = self::create_cmdxml($xml_data);
	}

	public function sendHeader($type=1)
	{
		$type = str_pad($type, 2, '0', STR_PAD_LEFT);
		$len = strlen($this->cmdxml);
		$length = str_pad($len, 6, '0', STR_PAD_LEFT);

		$this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

		$conn_rst = socket_connect($this->socket, $this->host, $this->port);
		if($conn_rst < 0) {
			// return 'socket_connect() failed./nReason: ($conn_rst) ' . socket_strerror($conn_rst);
			return false;
		}

		$write_rst = socket_write($this->socket, $type.$length, 8);
		if(!$write_rst) {
			// return 'socket_write() failed: reason: ' . socket_strerror($write_rst);
			return false;
		}

		while($response = socket_read($this->socket, 8192))
		{
			return new \SimpleXMLElement($response);
		}
	}

	public function send($type=1)
	{
		$this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

		if($this->socket<0) {
			// return 'socket_create() failed: reason: ' . socket_strerror($this->socket);
			return false;
		}

		$conn_rst = socket_connect($this->socket, $this->host, $this->port);
		if($conn_rst < 0) {
			// return 'socket_connect() failed./nReason: ($conn_rst) ' . socket_strerror($conn_rst);
			return false;
		}

		// send header
		$type = str_pad($type, 2, '0', STR_PAD_LEFT);
		$len = strlen($this->cmdxml);
		$length = str_pad($len, 6, '0', STR_PAD_LEFT);

		if ( socket_write($this->socket, $type.$length, strlen($type.$length)) == false ) {
			throw new Exception( sprintf( "Unable to write to socket: %s", socket_strerror( socket_last_error() ) ) );
		}

		$write_rst = socket_write($this->socket, $this->cmdxml, strlen($this->cmdxml));
		if(!$write_rst) {
			// return 'socket_write() failed: reason: ' . socket_strerror($write_rst);
			return false;
		}

		$header_recv = socket_read($this->socket, 8);

		if($header_recv) {
			while($response = socket_read($this->socket, $header_recv))
			{
				return new \SimpleXMLElement($response);
			}
		} else {
			dd($header_recv);
		}
	}

	public function close()
	{
		if($this->socket) {
			socket_close($this->socket);
			return true;
		} else {
			return false;
		}
	}

	public function create_cmdxml($xml_data)
	{
		$template= <<<XML
<?xml version='1.0' encoding='utf-8'?>
<CMD></CMD>
XML;
		$xml = new \SimpleXMLElement($template);

		$xml->addChild('module', $xml_data['module']);
		$xml->addChild('func', $xml_data['func']);
		$info = $xml->addChild('info');
		
		foreach($xml_data['info'] as $key => $val){
			if (is_array($val)) {
				foreach ($val as $subkey => $subval) {
					$info->addChild($key, $subval);
				}
			} else {
				$info->addChild($key, $val);
			}
		}

		return $xml->asXML();
	}

	public function create_element($xml, $array)
	{
		foreach($array as $key => $val){
			$item = $xml->addChild($key, $val);
		}
	}





	public function test()
	{
		var_dump($this->cmdxml);
	}
}