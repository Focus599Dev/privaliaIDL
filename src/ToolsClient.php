<?php 

namespace Focus599Dev\privaliaIDL;

use Focus599Dev\privaliaIDL\Common\Tools as BaseTools;
use Focus599Dev\privaliaIDL\Common\DOMImproved as Dom;

class ToolsClient extends BaseTools {

	private $server;

	private $dom;

	public function __construct($tAmb){

		parent::__construct($tAmb);

		$parameters = array(
			'location' => $this->url[$this->tAmb],
			'uri' => $this->uri[$this->tAmb],
			'trace' => 1
		);

		$this->server = new \SoapClient(NULL, $parameters);

		if (!$this->dom)
			$this->clearDom();

	}

	private function clearDom(){
		
		$this->dom = new Dom('1.0', 'UTF-8');
        
        $this->dom->preserveWhiteSpace = false;
        
        $this->dom->formatOutput = false;

	}

	public function sendNFe($nrPedido, $chave, $xml){

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

        $this->dom->appendChild($recebeNFe);

        return $this->sendRequest('setRecebeStatusFat', $this->dom->saveXML());

	}

	private function sendRequest ($method, $data){
		
		$response = null;

		$data = trim(preg_replace("/<\?xml.*?\?>/", "", $data));

		$this->request = $data;

		try{

			$response = $this->server->__soapCall($method,  [
	   			new \SoapParam($data, 'root')
			]);


            $this->response = $response;


		} catch(\SoapFault $e){

            $this->response = $this->server->__getLastResponse();

            $this->response = $this->removeStuffs($this->response);

            $this->response = str_replace('ns1:', '', $this->response);

            return simplexml_load_string($this->response);
			
		}

        return $this->response;
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
            $nrPedido,
            true,
            "Número do pedido"
        );

        $this->dom->addChild(
            $cancelamentoNFe,
            "tipoCancelamento",
            'PEDIDO',
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

        		$this->dom->appChild($pedidoXML, $itemXML, 'Falta tag "pedido"');

        	}
        }

        $this->dom->appendChild($pedidoXML);

        return $this->sendRequest('setEnvPedido', $this->dom->saveXML());

	}

    public function removeStuffs($xml){     

        $tag = '<SOAP-ENV:Body>';

        $xml = substr( $xml, ( strpos($xml, $tag) + strlen($tag) ), strlen($xml)  );
            
        $tag = '</SOAP-ENV:Body>';

        $xml = substr( $xml, 0 , strpos($xml, $tag) );

        $find = array(
            ' xsi:type="xsd:string"',
            ' xmlns:ns1="http://177.126.188.66/WSPrivalia"',
            ' xmlns:ns1="http://177.126.188.77/WSPrivalia"',
            ' xsi:type="tns:arrEnvPedidoReturn"',
            ' xsi:type="xsd:integer"',
            ' xsi:type="xsd:integer"',
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

        return $xml;
    }	

}

?>
