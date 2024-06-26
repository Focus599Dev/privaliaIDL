<?php 

namespace Focus599Dev\privaliaIDL;

use Focus599Dev\privaliaIDL\Common\Tools as BaseTools;
use Focus599Dev\privaliaIDL\Common\DOMImproved as Dom;
use SoapFault;

class ToolsClient extends BaseTools {

	private $server;

	private $dom;

    protected $wsdl;

    protected $centro;

    protected $auth = array(
        'user' => '',
        'password' => '',
    );

	public function __construct($tAmb, $centro){

		parent::__construct($tAmb);

		$parameters = array(
			'location' => $this->url[$centro][$this->tAmb],
			'uri' => $this->uri[$centro][$this->tAmb],
			'trace' => 1,
		);

        $this->wsdl = null;

        if (isset($this->wsdlList[$centro][$this->tAmb])){
            
            $this->wsdl = $this->wsdlList[$centro][$this->tAmb];

        }

        $this->centro = $centro;

		$this->server = new \SoapClient($this->wsdl, $parameters);

		if (!$this->dom)
			$this->clearDom();

	}

	private function clearDom(){
		
		$this->dom = new Dom('1.0', 'UTF-8');
        
        $this->dom->preserveWhiteSpace = false;
        
        $this->dom->formatOutput = false;

	}

	public function sendNFe($nrPedido, $chave, $xml, $pdf = ''){

		$this->clearDom();
		
		$recebeNFe = $this->dom->createElement("recebeNFe");

		$xml = '<![CDATA[' . $xml . ']]>';

		$this->dom->addChild(
            $recebeNFe,
            "nrPedido",
            $nrPedido,
            true,
            "Número do pedido"
        );

        $this->dom->addChild(
            $recebeNFe,
            "chaveNFe",
            $chave,
            true,
            "Chave NFe"
        );


        $this->dom->addChild(
            $recebeNFe,
            "xmlNFe",
            $xml,
            true,
            "XML NFe"
        );
		
	if ($pdf){

            $this->dom->addChild(
                $recebeNFe,
                "pdfNfeSimp",
                $pdf,
                true,
                "Danfe Simplificada"
            );

        }	

        $this->dom->appendChild($recebeNFe);

        $xml = $this->dom->saveXML();

        $xml = str_replace(array(
            '&lt;![CDATA[',
            ']]&gt;'
        ),
        array(
            '<![CDATA[',
            ']]>'
        ), $xml);

        return $this->sendRequest('setRecebeStatusFat', $xml);

	}

    private function sendRequestSoap($method, $data){

        $response = null;

		$data = trim(preg_replace("/<\?xml.*?\?>/", "", $data));

		$this->request = $data;

		try{

            if ($this->wsdl){

                $response = $this->server->{$method}(array('sXml' => $data));

                $fieldResponse = $method . 'Result';

                if ( isset($response->{$fieldResponse}) ){
                    
                    $response = $response->{$fieldResponse};

                    $response = $this->removeStuffs($response);

                    $response = simplexml_load_string($response);
                }

            } else {
                
                $response = $this->server->__soapCall($method,  [
                    new \SoapParam($data, 'root')
                ]);

            }

            $this->response = $response;


		} catch(SoapFault $e){

            $this->response = $this->server->__getLastResponse();

            $this->response = $this->removeStuffs($this->response);

            $this->response = str_replace('ns1:', '', $this->response);

            error_clear_last();
		try{
			
            		return simplexml_load_string($this->response);
		} catch(\Exception $e){
			return false;
		}
		}
	
        return $this->response;

    }

    private function urlAPI($method){

        $urls = array(
            'setEnvPedido' => 'https://156.137.46.15/exchange/ZZAIHMQTEST/Privalia_BR/M50_Order',
            'setEstrategiaLiberacao' => 'https://156.137.46.15/exchange/ZZAIHMQTEST/Privalia_BR/R41_BatchSts',
            'setRecebeStatusFat' => 'https://156.137.46.15/exchange/ZZAIHMQTEST/Privalia_BR/NFe_OrdUpd',
        );

        if (isset($urls[$method]))
            return $urls[$method];

        return null;
    }

    private function sendRequestApi($method, $data){

        $url = $this->urlAPI($method);

        if (!$url)
            return null;

        $ch = curl_init( $url );

        $data = $this->makeEnvelopeAPI($data);

        $msgSize = strlen($data);

        $headers = array(
            'Content-Type' => 'application/json',
            'Content-length' => $msgSize,
            'Authorization' => 'Basic ' . base64_encode($this->auth['user'] . ':' . $this->auth['password'])
        );

        curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );

        curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers);

        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

        // curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);

        // curl_setopt($ch, CURLOPT_TIMEOUT, 30 + 20);

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

        curl_setopt($ch, CURLOPT_TIMEOUT, 5 );

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);

        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        switch($httpcode){
            case '200':
                return json_decode('{"codigoRetorno":"100","nrPedido":"","msgRetorno":"OK"}');
            break;
            case '':
                return null;
            default:
                return json_decode('{"codigoRetorno":"' . $httpcode . '","nrPedido":"","msgRetorno":"' . $response . '"}');

        }
    }

    public function setAuth($user, $pass){

        $this->auth = array(
            'user' => $user,
            'password' => $pass,
        );
    }

    private function makeEnvelopeAPI($data){

        $data = trim(preg_replace("/<\?xml.*?\?>/", "", $data));

        return '
        <SOAP:Envelope xmlns:SOAP="http://schemas.xmlsoap.org/soap/envelope/">
            <SOAP:Header/>
            <SOAP:Body>
                ' . $data . '
            </SOAP:Body>
        </SOAP:Envelope>
        ';
    }

	private function sendRequest ($method, $data){

        $centroToSendApi = array(
            '0580'
        );

        if (in_array($this->centro, $centroToSendApi)){

            return $this->sendRequestApi($method, $data);
        }
		
        return $this->sendRequestSoap($method, $data);
		
	}

	public function cancelNFe($nrPedido){

		$this->clearDom();
		
		$cancelamentoNFe = $this->dom->createElement("cancelamentoNFe");

		$this->dom->addChild(
            $cancelamentoNFe,
            "nrPedido",
            $nrPedido,
            true,
            "Número do pedido"
        );

        $this->dom->addChild(
            $cancelamentoNFe,
            "tipoCancelamento",
            'NFE',
            true,
            "tipo de cancelamento"
        );

        $this->dom->appendChild($cancelamentoNFe);

        return $this->sendRequest('setCancelamentoNFe', utf8_encode($this->dom->saveXML()));

	}

    public function cancelPedido(\StdClass $pedido){

        $this->clearDom();
        
        $cancelamentoNFe = $this->dom->createElement("cancelamentoNFe");

        $this->dom->addChild(
            $cancelamentoNFe,
            "nrPedido",
            $pedido->nrPedido,
            true,
            "Número do pedido"
        );

        $this->dom->addChild(
            $cancelamentoNFe,
            "tipoCancelamento",
            $pedido->typePedido,
            true,
            "tipo de cancelamento"
        );

        $this->dom->appendChild($cancelamentoNFe);

        return $this->sendRequest('setCancelamentoNFe', utf8_encode($this->dom->saveXML()));

    }

	public function enviaPedido(\StdClass $pedido){

		$this->clearDom();
		
		$pedidoXML = $this->dom->createElement("pedido");
		
		$cliente = $this->dom->createElement("cliente");

		$this->dom->addChild(
            $pedidoXML,
            "nrPedido",
            $pedido->nrPedido,
            true,
            "Número do pedido"
        );

        $this->dom->addChild(
            $pedidoXML,
            "cnpjTransportador",
            $pedido->cnpjTransportador,
            true,
            "CNPJ Transportador"
        );

        $this->dom->addChild(
            $pedidoXML,
            "dataPedido",
            $pedido->dataPedido,
            true,
            "Data pedido"
        );

        $this->dom->addChild(
            $pedidoXML,
            "nrCampanha",
            $pedido->nrCampanha,
            true,
            "Numero campanha"
        );

        $this->dom->addChild(
            $pedidoXML,
            "nrTipoPedido",
            $pedido->nrTipoPedido,
            true,
            "Numero tipo de pedido"
        );

        $this->dom->addChild(
            $pedidoXML,
            "tipoRoteirizacao",
            $pedido->tipoRoteirizacao,
            true,
            "Tipo roterização"
        );

        $this->dom->addChild(
            $pedidoXML,
            "valorRoteirizacao",
            $pedido->valorRoteirizacao,
            true,
            "Valor roteriação"
        );

        $this->dom->addChild(
            $pedidoXML,
            "deposito",
            $pedido->deposito,
            true,
            "Numero deposito"
        );

        $this->dom->addChild(
            $pedidoXML,
            "ordemVenda",
            $pedido->ordemVenda,
            true,
            "Ordem de venda"
        );
		
	    $this->dom->addChild(
            $pedidoXML,
            "primeiraCompra",
            $pedido->primeiraCompra,
            true,
            "primeira Compra"
        );	

        $this->dom->addChild(
            $cliente,
            "nomeCliente",
            $pedido->cliente->nomeCliente,
            true,
            "Nome do cliente"
        );

        $this->dom->addChild(
            $cliente,
            "cnpjCPF",
            $pedido->cliente->cnpjCPF,
            true,
            "CPF/CNPJ cliente"
        );

        $this->dom->addChild(
            $cliente,
            "logradouro",
            $pedido->cliente->logradouro,
            true,
            "endereço cliente"
        );

        $this->dom->addChild(
            $cliente,
            "nrLogradouro",
            $pedido->cliente->nrLogradouro,
            true,
            "numero do endereço cliente"
        );

        $this->dom->addChild(
            $cliente,
            "bairro",
            $pedido->cliente->bairro,
            true,
            "baido do endereço cliente"
        );

        $this->dom->addChild(
            $cliente,
            "cidade",
            $pedido->cliente->cidade,
            true,
            "cidade cliente"
        );

        $this->dom->addChild(
            $cliente,
            "uf",
            $pedido->cliente->uf,
            true,
            "uf cliente"
        );

        $this->dom->addChild(
            $cliente,
            "cep",
            $pedido->cliente->cep,
            true,
            "CEP cliente"
        );

        $this->dom->appChild($pedidoXML, $cliente, 'Falta tag "pedido"');

        if ($pedido->items){

        	foreach ($pedido->items as $item) {
        		
				$itemXML = $this->dom->createElement("item");

				$this->dom->addChild(
			        $itemXML,
			        "codItem",
			        $item->codItem,
			        true,
			        "Codigo do item"
			    );

			    $this->dom->addChild(
			        $itemXML,
			        "linhaItem",
			        $item->linhaItem,
			        true,
			        "Linha item"
			    );
			    
			    $this->dom->addChild(
			        $itemXML,
			        "qtde",
			        $item->qtde,
			        true,
			        "quantidade de item"
			    );

			    $this->dom->addChild(
			        $itemXML,
			        "loteSAP",
			        $item->loteSAP,
			        true,
			        "Lote SAP"
			    );

			    $this->dom->addChild(
			        $itemXML,
			        "eanItem",
			        $item->eanItem,
			        true,
			        "Ean Item"
			    );

			    $this->dom->addChild(
			        $itemXML,
			        "descricaoItem",
			        $item->descricaoItem,
			        true,
			        "Descrição do Item"
			    );

			    $this->dom->addChild(
			        $itemXML,
			        "precoItem",
			        $item->precoItem,
			        true,
			        "Preço Item"
			    );
			
			     $this->dom->addChild(
			        $itemXML,
			        "zcam",
			        $item->zcam,
			        false,
			        "zcam"
			    );

        		$this->dom->appChild($pedidoXML, $itemXML, 'Falta tag "pedido"');

        	}
        }

        $this->dom->appendChild($pedidoXML);

        return $this->sendRequest('setEnvPedido', $this->dom->saveXML());

	}

    public function estrategiaLiberacao(\StdClass $pedido){

        $this->clearDom();
        
        $estrategiaLiberacao = $this->dom->createElement("estrategiaLiberacao");

        $this->dom->addChild(
            $estrategiaLiberacao,
            "nrPedidoCompra",
            $pedido->nrPedido,
            true,
            "Pedido de compra"
        );

        $this->dom->addChild(
            $estrategiaLiberacao,
            "campanha",
            $pedido->campanha,
            true,
            "campanha"
        );        

         $this->dom->appendChild($estrategiaLiberacao);

        return $this->sendRequest('setEstrategiaLiberacao', $this->dom->saveXML());

    }

    public function removeStuffs($xml){     

        $tag = '<SOAP-ENV:Body>';

        $pos1 = strpos($xml, $tag);

        if ($pos1 !== false){
            $xml = substr( $xml, ( $pos1 + strlen($tag) ), strlen($xml)  );
        }
            
        $tag = '</SOAP-ENV:Body>';

        $pos1 = strpos($xml, $tag);

        if ($pos1 !== false){
            $xml = substr( $xml, 0 , strpos($xml, $tag) );
        }

        $find = array(
            ' xsi:type="xsd:string"',
            ' xmlns:ns1="http://177.126.188.66/WSPrivalia"',
            ' xmlns:ns1="http://177.126.188.77/WSPrivalia"',
            ' xsi:type="tns:arrEnvPedidoReturn"',
            ' xsi:type="xsd:integer"',
            ' xsi:type="xsd:integer"',
            ' xmlns:xsd="http://www.w3.org/2001/XMLSchema"',
            ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"',
        );

        $replace = array(
            '',
            '',
            '',
            '',
            '',
            '',
        );

        $xml = str_replace($find, $replace, $xml);

        $xml = preg_replace('/ xsi:type="[a-zA-Z0-9:;\.\s\(\)\-\,]*"/', '', $xml);

        $xml = trim(preg_replace("/<\?xml.*?\?>/", "", $xml));

        return $xml;
    }	

}

?>
