<?php 

namespace Focus599Dev\privaliaIDL\Common;

class Tools {

	protected $url = array(
		1 => 'http://177.126.188.77/WSPrivalia/wsPrivalia.php',
		2 => 'http://177.126.188.66/WSPrivalia/wsPrivalia.php'
	);

	protected $uri = array(
		1 => 'http://177.126.188.77/WSPrivalia',
		2 => 'http://177.126.188.66/WSPrivalia'
	);

	protected $tAmb = 2;

	public $request;

	public $response;

	public function __construct($tAmb){

		if ($tAmb)
			$this->tAmb = $tAmb;

	}

}

?>
