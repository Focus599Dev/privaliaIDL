<?php 

namespace Focus599Dev\privaliaIDL\Common;

class Tools {

	protected $url = array(
		'0550' => array(
			1 => 'http://177.126.188.77/WSPrivalia/wsPrivalia.php',
			2 => 'http://177.126.188.66/WSPrivalia/wsPrivalia.php'
		),
		'0560' => array(
			1 => 'http://177.126.188.77/WSPrivalia/wsPrivalia.php',
			2 => 'http://177.126.188.66/WSPrivalia/wsPrivalia.php'
		),
		'0530' => array(
			1 => 'http://wsprivalia.sequoialog.com.br:8282/SrvPrivalia.svc',
			2 => 'http://wsprivalia.sequoialog.com.br:8282/SrvPrivalia.svc'
		)
	);

	protected $wsdlList = array(
		'0550' => array(
			1 => NULL,
			2 => NULL
		),
		'0560' => array(
			1 => NULL,
			2 => NULL
		),
		'0530' => array(
			1 => 'http://wsprivalia.sequoialog.com.br:8282/SrvPrivalia.svc?wsdl',
			2 => 'http://wsprivalia.sequoialog.com.br:8282/SrvPrivalia.svc?wsdl'
		)
	);

	protected $uri = array(
		'0550' => array(
			1 => 'http://177.126.188.77/WSPrivalia',
			2 => 'http://177.126.188.66/WSPrivalia'
		),
		'0560' => array(
			1 => 'http://177.126.188.77/WSPrivalia',
			2 => 'http://177.126.188.66/WSPrivalia'
		),
		'0530' => array(
			1 => 'http://wsprivalia.sequoialog.com.br:8282',
			2 => 'http://wsprivalia.sequoialog.com.br:8282'
		)
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
