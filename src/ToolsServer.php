<?php 

namespace Focus599Dev\privaliaIDL;

use Focus599Dev\privaliaIDL\Common\Tools as BaseTools;

class ToolsServer{

	private $server;

	private $urlSever;

	public function __construct($url){

		$this->urlSever = $url;

		$parameters = array(
			'uri' => $this->urlSever,
		);

		$this->server = new \SoapServer(NULL, $parameters);

	}

	public function setClass($classHandle){

		$this->server->setClass($classHandle);

	}

	public function handle(){

		$this->server->handle();
	}

	public function getServer(){

		return $this->server;
		
	}
}

?>
