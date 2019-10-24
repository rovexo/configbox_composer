<?php
    /**
     * @author              support@mpay24.com
     * @version             $Id: MPay24Api.php 5294 2013-01-17 11:54:45Z anna $
     * @filesource          MPay24Api.php
     * @license             http://ec.europa.eu/idabc/eupl.html EUPL, Version 1.1
     */
    
    class MPay24Api {

        /**
         * @var         BOOLEAN                            TRUE, when you want to use the test system, and FALSE otherwise
         */
        private $test             = false;
        /**
         * @var         STRING                            The link where the requests should be sent to
         * 
         *                                                 DEFAULT : https://test.mpay24.com/app/bin/etpproxy_v14 (TEST SYSTEM)
         */
        private $etp_url         = "https://test.mpay24.com/app/bin/etpproxy_v15";
        /**
         * @var         INTEGER                            The merchant ID (supported from mPAY24). 5-digit number. Begin with 9 
         *                                                 for test system, begin with 7 for the live system.
         */
        private $merchantid         = "9xxxx";
        /**
         * @var         STRING                            The SOAP password (supproted from mPAY24)
         */
        private $soappass         = "";
        /**
         * @var         STRING                            The fix (envelope) part of the soap xml, which is to be sent to mPAY24
         */
        private $soap_xml        = "";
        /**
         * @var         STRING                            The host name, in case you are using proxy
         */
        private $proxy_host        = "";
        /**
         * @var         INTEGER                            4-digit port number, in case you are using proxy
         */
        private $proxy_port        = "";
        /**
         * @var         STRING                            The whole soap-xml (envelope and body), which is to be sent to mPAY24 as request
         */
        private $request        = "";
        /**
         * @var         STRING                            The response from mPAY24
         */
        private $response         = "";
        /**
         * @var         BOOLEAN                            TRUE if log files are to be written, by default - FALSE
         */
        private $debug         = true;
        
        
        /**
         * @abstract                                    Set the basic (mandatory) settings for the requests
         * @param         INTEGER                            5-digit account number, supported by mPAY24
         * 
         *                                                 TEST accounts - starting with 9
         * 
         *                                                 LIVE account - starting with 7
         * @param         STRING                            The webservice's password, supported by mPAY24
         * @param         BOOLEAN                            TRUE - when you want to use the TEST system
         * 
         *                                                 FALSE - when you want to use the LIVE system                
         * @param         STRING                            The host name in case you are behind a proxy server ("" when not)
         * @param         INTEGER                            4-digit port number in case you are behind a proxy server ("" when not)
         */
        public function configure($merchantID, $soapPassword, $test, $proxyHost, $proxyPort) {
        	define('LIVE_ERROR_MSG', "We are sorry, an error occured - please contact the merchant!");
        	
        	if ( !defined('__DIR__') ) define('__DIR__', dirname(__FILE__));
            
        	$this->setMerchantID($merchantID);
            $this->setSoapPassword($soapPassword);
            $this->setSystem($test);
            
            if($proxyHost != "" && $proxyPort != "")
                $this->setProxySettings($proxyHost, $proxyPort);
        }
        
        /**
         * @return        STRING                            5-digit merchant ID                                
         */
        public function getMerchantID() {
            return substr($this->merchantid, 1);            
        }
        
        /**
         * @return        STRING                            The URL where the requests are sending to                                
         */
        public function getEtpURL() {
            return $this->etp_url;            
        }
        
        /**
         * @return        STRING                            The request, which was sent to mPAY24 (in XML form)
         */
        public function getRequest() {
            return $this->request;
        }

        /**
         * @return        STRING                            The response from mPAY24 (in XML form)
         */
        public function getResponse() {
            return $this->response;
        }
        
        /**
         * @return        BOOLEAN                            Whether a proxy is used
         */
        public function proxyUsed() {
            if($this->proxy_host != '' && $this->proxy_port != '')
                return true;
            else
                return false;
        }
        
        /**
         * @abstract                                    Set debug modus (FALSE by default)
         * @param         BOOLEAN                       TRUE if is turned on, otherwise FALSE
         */
        public function setDebug($debug) {
        	$this->debug = $debug;  	
        }
        
        /**
         * @return        BOOLEAN                            Whether the debug modus is turned on or off
         */
        public function getDebug() {
        	return $this->debug;
        }
        
        public function dieWithMsg($msg) {
			if($this->test)
        		die($msg);
			else
            	die(LIVE_ERROR_MSG);
        }
        
		public function printMsg($msg) {
			if($this->test)
				print($msg);
			else
	            print(LIVE_ERROR_MSG);
		}
        
		public function permissionError() {
			$errors = error_get_last();
			$message = $errors['message'];
			$path = substr($message, strpos($message, 'fopen(')+6, strpos($message, ')')-(strpos($message, 'fopen(')+6));
			$this->dieWithMsg("Can't open file '$path'! Please set the needed read/write rights!");
		}
		
        /**
         * @abstract                                    Get all the payment methods, that are available for the merchant by mPAY24
         * @uses        ListPaymentMethodsResponse
         * @return        ListPaymentMethodsResponse
         */
        public function ListPaymentMethods() {
			$xml = $this->buildEnvelope();
			$body = $xml->getElementsByTagNameNS('http://schemas.xmlsoap.org/soap/envelope/', 'Body')->item(0);
             
			$operation = $xml->createElementNS('https://www.mpay24.com/soap/etp/1.5/ETP.wsdl', 'etp:ListPaymentMethods');
            $operation = $body->appendChild($operation);
            
            $xmlMerchantID = $xml->createElement('merchantID', substr($this->merchantid, 1));
            $xmlMerchantID = $operation->appendChild($xmlMerchantID);
            
            $this->request  = $xml->saveXML();
            
            $this->send();
            
            $result = new ListPaymentMethodsResponse($this->response);

            return $result;
        }
        
        /**
         * @abstract                                    Start a secure payment through the mPAY24 payment window - 
         *                                                 the sensible data (credit card numbers, bank account numbers etc)
         *                                                 is (will be) not saved in the shop
         * @uses        PaymentResponse
         * @param         ORDER                             The mdxi xml, which contains the shopping cart
         * @return        PaymentResponse
         */
        public function SelectPayment($mdxi) {
            $xml = $this->buildEnvelope();
			$body = $xml->getElementsByTagNameNS('http://schemas.xmlsoap.org/soap/envelope/', 'Body')->item(0);
             
			$operation = $xml->createElementNS('https://www.mpay24.com/soap/etp/1.5/ETP.wsdl', 'etp:SelectPayment');
            $operation = $body->appendChild($operation);
            
            $merchantID = $xml->createElement('merchantID', substr($this->merchantid, 1));
            $merchantID = $operation->appendChild($merchantID);
        
            $xmlMDXI = $xml->createElement('mdxi', htmlspecialchars($mdxi));
            $xmlMDXI = $operation->appendChild($xmlMDXI);    

            $getDataURL = $xml->createElement('getDataURL', "dummy_getDataURL");
            $getDataURL = $operation->appendChild($getDataURL);    
            
            $tid = $xml->createElement('tid', 'tid');
            $tid = $operation->appendChild($tid);

            $this->request  = $xml->saveXML();
            
            $this->send();
            
            $result = new PaymentResponse($this->response);
                        
            return $result;
        }
        
        /**
         * @abstract                                    Start a secure payment using a PROFILE (mPAY24 proSAFE), supported by mPAY24 - 
         *                                                 a customer profile (you have already created) will be used for the payment. 
         *                                                 The payment window will not be called, the payment source (for example credit card), 
         *                                                 which was used from the customer by the last payment will be used for the transaction.  
         * @uses        PaymentResponse
         * @param         ORDER                            The order xml, which contains the shopping cart
         * @return        SelectPaymentResponse
         */
        public function ProfilePayment($requestString) {
			$xml = $this->buildEnvelope();
			$body = $xml->getElementsByTagNameNS('http://schemas.xmlsoap.org/soap/envelope/', 'Body')->item(0);

			$operation = $xml->createElementNS('https://www.mpay24.com/soap/etp/1.5/ETP.wsdl', 'etp:AcceptPayment');
            $operation = $body->appendChild($operation);
            
            $requestXML = new DOMDocument("1.0", "UTF-8");
            $requestXML->formatOutput = true;
            $requestXML->loadXML($requestString);

            $requestNode = $requestXML->getElementsByTagName("AcceptPayment")->item(0);
            
            foreach($requestNode->childNodes as $child) {
	            $child = $xml->importNode($child, true);
	            $operation->appendChild($child);
            }

            $this->request  = $xml->saveXML();
            
            $this->send();
            
            $result = new PaymentResponse($this->response);

            return $result;
        }
        
        /**
         * @abstract                                    Start a secure payment using a PayPal Express Checkout, supported by mPAY24 - 
         *                                                 the customer doesn't need to be logged in in the shop or to give any data 
         *                                                 (addresses or payment information), but will be redirected to the PayPal site,
         *                                                 and all the information from PayPal will be taken for the payment.
         * @uses        PaymentResponse
         * @param         ORDER                            The order xml, which contains the shopping cart
         * @return        PaymentResponse
         */
        public function ExpressCheckoutPayment($requestString) {
            $xml = $this->buildEnvelope();
			$body = $xml->getElementsByTagNameNS('http://schemas.xmlsoap.org/soap/envelope/', 'Body')->item(0);
             
			$operation = $xml->createElement('etp:AcceptPayment');
            $operation = $body->appendChild($operation);
            
            $requestXML = new DOMDocument("1.0", "UTF-8");
            $requestXML->formatOutput = true;
            $requestXML->loadXML($requestString);

        	$requestNode = $requestXML->getElementsByTagName("AcceptPayment")->item(0);
            
            foreach($requestNode->childNodes as $child) {
	            $child = $xml->importNode($child, true);
	            $operation->appendChild($child);
	            if($child->nodeName == 'payment') {
	            	$child->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance' ,'xsi:type', 'etp:PaymentPAYPAL');
	            }
            }
            
            $this->request  = $xml->saveXML();
            
            $this->send();
            
            $result = new PaymentResponse($this->response);

            return $result;
        }
        
        /**
         * @uses        PaymentResponse                            
         * @param         STRING                            5-digit account number, supported by mPAY24
         * @param         STRING                            The transaction ID, supported from mPAY24
         * @param         INTEGER                            The positive shipping costs for the transaction multiply by 100
         * @param         INTEGER                            The positive amount to be reserved/billed multiply by 100
         * @param        STRING                            In case of "true", the transaction will be canceled, in case of "false" the amount will be reserved
         * @return        PaymentResponse
         */
        public function CallbackPaypal($requestString) {
            $xml = $this->buildEnvelope();
			$body = $xml->getElementsByTagNameNS('http://schemas.xmlsoap.org/soap/envelope/', 'Body')->item(0);
             
			$operation = $xml->createElement('etp:ManualCallback');
			$operation = $body->appendChild($operation);
			
			$requestXML = new DOMDocument("1.0", "UTF-8");
			$requestXML->formatOutput = true;
			$requestXML->loadXML($requestString);
			
			$requestNode = $requestXML->getElementsByTagName("AcceptPayment")->item(0);
			
			foreach($requestNode->childNodes as $child) {
				$child = $xml->importNode($child, true);
				$operation->appendChild($child);
				if($child->nodeName == 'paymentCallback') {
					$child->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance' ,'xsi:type', 'etp:CallbackPAYPAL');
				}
			}
            
            $this->request  = $xml->saveXML();
            
            $this->send();
            
            $result = new PaymentResponse($this->response);

            return $result;
            
        }
        
        /**
         * @abstract                                    Clear a transaction with an amount
         * @uses        ManagePaymentResponse
         * @param         INTEGER                         The mPAY24 transaction ID
         * @param         INTEGER                            The amount to be cleared multiplay by 100
         * @param         STRING                            3-digit ISO currency code: EUR, USD, etc
         * @return        ManagePaymentResponse            
         */
        public function ManualClear($mPAYTid, $amount, $currency) {
            $xml = $this->buildEnvelope();
			$body = $xml->getElementsByTagNameNS('http://schemas.xmlsoap.org/soap/envelope/', 'Body')->item(0);
             
			$operation = $xml->createElementNS('https://www.mpay24.com/soap/etp/1.5/ETP.wsdl', 'etp:ManualClear');
            $operation = $body->appendChild($operation);
            
            $merchantID = $xml->createElement('merchantID', substr($this->merchantid, 1));
            $merchantID = $operation->appendChild($merchantID);
        
            $clearingDetails = $xml->createElement('clearingDetails');
            $clearingDetails = $operation->appendChild($clearingDetails);
            
            $xmlMPayTid = $xml->createElement('mpayTID', $mPAYTid);
            $xmlMPayTid = $clearingDetails->appendChild($xmlMPayTid);
            
            $price = $xml->createElement('amount', $amount);
            $price = $clearingDetails->appendChild($price);
            
            $this->request  = $xml->saveXML();
            
            $this->send();

            $result = new ManagePaymentResponse($this->response);

            return $result;
        }
        
        /**
         * @abstract                                    Credit a transaction with an amount
         * @uses        ManagePaymentResponse
         * @param         INTEGER                         The mPAY24 transaction ID
         * @param         INTEGER                            The amount to be credited multiplay by 100
         * @param         STRING                            3-digit ISO currency code: EUR, USD, etc
         * @param         STRING                            The name of the customer, who has paid
         * @return        ManagePaymentResponse            
         */
        public function ManualCredit($mPAYTid, $amount, $currency, $customer) {
            $xml = $this->buildEnvelope();
			$body = $xml->getElementsByTagNameNS('http://schemas.xmlsoap.org/soap/envelope/', 'Body')->item(0);
             
			$operation = $xml->createElementNS('https://www.mpay24.com/soap/etp/1.5/ETP.wsdl', 'etp:ManualCredit');
            $operation = $body->appendChild($operation);
            
            $merchantID = $xml->createElement('merchantID', substr($this->merchantid, 1));
            $merchantID = $operation->appendChild($merchantID);    
            
            $xmlMPayTid = $xml->createElement('mpayTID', $mPAYTid);
            $xmlMPayTid = $operation->appendChild($xmlMPayTid);    
            
            $price = $xml->createElement('amount', $amount);
            $price = $operation->appendChild($price);
            
            $this->request  = $xml->saveXML();
            
            $this->send();

            $result = new ManagePaymentResponse($this->response);

            return $result;
        }
        
        /**
         * @abstract                                    Cancel a transaction
         * @uses        ManagePaymentResponse
         * @param         INTEGER                         The mPAY24 transaction ID for the transaction you want to cancel
         * @return        ManagePaymentResponse            
         */
        public function ManualReverse($mPAYTid) {
            $xml = $this->buildEnvelope();
			$body = $xml->getElementsByTagNameNS('http://schemas.xmlsoap.org/soap/envelope/', 'Body')->item(0);
             
			$operation = $xml->createElementNS('https://www.mpay24.com/soap/etp/1.5/ETP.wsdl', 'etp:ManualReverse');
            $operation = $body->appendChild($operation);
            
            $merchantID = $xml->createElement('merchantID', substr($this->merchantid, 1));
            $merchantID = $operation->appendChild($merchantID);    
            
            $xmlMPayTid = $xml->createElement('mpayTID', $mPAYTid);
            $xmlMPayTid = $operation->appendChild($xmlMPayTid);        
            
            $this->request  = $xml->saveXML();
            
            $this->send();
            
            $result = new ManagePaymentResponse($this->response);

            return $result;
        }
        
        /**
         * @abstract                                    Get all the information for a transaction, supported by mPAY24
         * @uses        TransactionStatusResponse
         * @param         INTEGER                         The mPAY24 transaction ID
         * @param         STRING                            The transaction ID from your shop
         * @return        TransactionStatusResponse
         */
        public function TransactionStatus($mPAYTid=null, $tid=null) {
            $xml = $this->buildEnvelope();
			$body = $xml->getElementsByTagNameNS('http://schemas.xmlsoap.org/soap/envelope/', 'Body')->item(0);
             
			$operation = $xml->createElementNS('https://www.mpay24.com/soap/etp/1.5/ETP.wsdl', 'etp:TransactionStatus');
            $operation = $body->appendChild($operation);
            
            $merchantID = $xml->createElement('merchantID', substr($this->merchantid, 1));
            $merchantID = $operation->appendChild($merchantID);
        
            if($mPAYTid) {                    
                $xmlMPayTid = $xml->createElement('mpayTID', $mPAYTid);
                $xmlMPayTid = $operation->appendChild($xmlMPayTid);
            } else {
                $xmlTid = $xml->createElement('tid', $tid);
                $xmlTid = $operation->appendChild($xmlTid);
            }
            
            $this->request  = $xml->saveXML();
            
            $this->send();

            $result = new TransactionStatusResponse($this->response);

            return $result;
        }
    
        /**
         * @param         STRING: (without 'u')
         */
        private function setMerchantID($merchantID=null) {
            if($merchantID==null)
               $this->merchantid = 'u' . MERCHANT_ID;
            else
                $this->merchantid = 'u' . $merchantID;
        }
        
        /**
         * @param         STRING
         */
        private function setSoapPassword($pass=null) {
            if(defined("SOAP_PASSWORD"))
                $this->soappass = SOAP_PASSWORD;
            else
                $this->soappass = $pass;
        }    

        /**
         * @abstract                                    Set if the system is test (true) or not (false)
         * 
         *                                                 Set the POST url 
         *                                                 ("https://test.mpay24.com/app/bin/etpproxy_v14" or 
         *                                                 "https://www.mpay24.com/app/bin/etpproxy_v14")
         * @param         BOOLEAN
         *                     
         */
        private function setSystem($test=null) {
            if($test) {
               $this->test = true;
               $this->etp_url = "https://test.mpay24.com/app/bin/etpproxy_v15";
            } else {
                $this->test = false;
                $this->etp_url = "https://www.mpay24.com/app/bin/etpproxy_v15";
            }
        }
        
        /**
         * @abstract                                    Set the used proxy host and proxy port in case proxy is used
         * @param        STRING
         * @param        STRING
         */
        private function setProxySettings($proxy_host="", $proxy_port="") {
            if($proxy_host != "" && $proxy_port != "") {
               $this->proxy_host = $proxy_host;
               $this->proxy_port = $proxy_port;
            } 
        }
        
        /**
         * @abstract                                    Create a DOMDocument and prepare it for SOAP request:
         * 
         *                                                 set Envelope, NameSpaces, create empty Body
         * @return        DOMDocument
         */
        private function buildEnvelope() {
            $this->soap_xml = new DOMDocument("1.0", "UTF-8");
            $this->soap_xml->formatOutput = true;
            
            $envelope = $this->soap_xml->createElementNS('http://schemas.xmlsoap.org/soap/envelope/', 'soapenv:Envelope');
            $envelope->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsd', 'http://www.w3.org/2001/XMLSchema');
            $envelope->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:etp', 'https://www.mpay24.com/soap/etp/1.5/ETP.wsdl');
            $envelope->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
            $envelope = $this->soap_xml->appendChild($envelope);
            
            $body = $this->soap_xml->createElementNS('http://schemas.xmlsoap.org/soap/envelope/', 'soapenv:Body');
            $body = $envelope->appendChild($body);

            return $this->soap_xml;
        }

        /**
          * @uses        buildXMLRequest()
          * @abstract                                    Create a curl request and send the cretaed SOAP XML
          */
		private function send() { 
         	$userAgent = 'mPAY24 PHP API $Rev: 5294 $ ($Date:: 2013-01-17 #$)';
    
            $ch = curl_init($this->etp_url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch,CURLOPT_USERPWD,"$this->merchantid:$this->soappass"); 
            curl_setopt($ch,CURLOPT_USERAGENT,$userAgent);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->request);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            if($this->debug) {
              $fh = fopen(__DIR__."/curllog.log", 'a+') or $this->permissionError();
              		
              curl_setopt($ch, CURLOPT_VERBOSE, 1);
              curl_setopt($ch, CURLOPT_STDERR, $fh);
            }
            
            try {
            	curl_setopt($ch, CURLOPT_CAINFO, __DIR__.'/cacert.pem');
	            
	            if($this->proxy_host !== '' && $this->proxy_port !== '')
	                curl_setopt($ch, CURLOPT_PROXY, $this->proxy_host.':'.$this->proxy_port);
	                
                $this->response = curl_exec($ch);
                curl_close($ch);  
                if($this->debug)
                	fclose($fh);
            } catch (Exception $e) {
				if($this->test)
	               $dieMSG = "Your request couldn't be sent because of the following error:" . "\n" . 
	                         curl_error($ch) . "\n" . 
	                         $e->getMessage() . 
	                         ' in '.$e->getFile().', line: '.
                            $e->getLine().'.';
				else
					$dieMSG = LIVE_ERROR_MSG;
				
                echo $dieMSG;
            }
         }
    }
    
    
    
    class GeneralResponse {
        
        /**
         * @var         STRING                            The status of the request, which was sent to mPAY24
         */
        var $status;
        /**
         * 
         * @var         STRING                            The return code from the request, which was sent to mPAY24
         */
        var $returnCode;
        
        /**
         * @abstract                                    Sets the basic values from the response from mPAY24: status and return code
         * @param         STRING                            The SOAP response from mPAY24 (in XML form)
         */
        function GeneralResponse($response) {
        	if($response != '') {
						$responseAsDOM = new DOMDocument();
	          $responseAsDOM->loadXML($response);
	            
	          if(!empty($responseAsDOM) && is_object($responseAsDOM))
	          	if(!$responseAsDOM || $responseAsDOM->getElementsByTagName('status')->length == 0 || $responseAsDOM->getElementsByTagName('returnCode')->length == 0){
		            $this->status             = "ERROR";
		            $this->returnCode        = urldecode($response);
	          	} else {
		            $this->status             = $responseAsDOM->getElementsByTagName('status')->item(0)->nodeValue;
		            $this->returnCode         = $responseAsDOM->getElementsByTagName('returnCode')->item(0)->nodeValue;
	          	}    
            } else {
            	$this->status             = "ERROR";
              $this->returnCode         = "The response is empty! Probably your request to mPAY24 was not sent! Please see your server log for more information!";
            }        
        }
        
        /**
         * @return        STRING                            The status of the request, which was sent to mPAY24
         */
        public function getStatus() {
            return $this->status;
        }
        
        /**
         * @return        STRING                            The return code from the request, which was sent to mPAY24
         */
        public function getReturnCode() {
            return $this->returnCode;
        }

        public function setStatus($status) {
            $this->status = $status;
        }

        public function setReturnCode($returnCode) {
            return $this->returnCode = $returnCode;
        }
        
    }
    
    class PaymentResponse extends GeneralResponse {
        
        /**
         * @var         GeneralResponse                    An object, that represents the basic values from the response from mPAY24: 
         *                                                 status and return code
         */
        var $generalResponse;

        /**
         * @var         STRING                            An URL (of the mPAY24 payment fenster), where the customer would be redirected to, 
         *                                                 in case of successfull request
         */
        var $location;
        
        
        /**
         * @abstract                                    Sets the values for a payment from the response from mPAY24: 
         *                                                 mPAY transaction ID, error number and location (URL)
         * @param         STRING                            The SOAP response from mPAY24 (in XML form)
         */
        function PaymentResponse($response) {
        	$this->generalResponse = new GeneralResponse($response);
        	if($response != '') {	 
							$responseAsDOM = new DOMDocument();
	            $responseAsDOM->loadXML($response);
	            
	            if(!empty($responseAsDOM) && is_object($responseAsDOM) && $responseAsDOM->getElementsByTagName('location')->length != 0)       
	                $this->location            = $responseAsDOM->getElementsByTagName('location')->item(0)->nodeValue;
	         } else {
                $this->generalResponse->setStatus("ERROR");
                $this->generalResponse->setReturnCode("The response is empty! Probably your request to mPAY24 was not sent! Please see your server log for more information!");
            }    
        }
        
        /**
         * @return        STRING                            The location (URL), returned from mPAY24
         */
        public function getLocation(){
            return $this->location;
        }
        
        /**
         * @return        GeneralResponse                    The object, that contains the basic values from the response from mPAY24: 
         *                                                 status and return code
         */
        public function getGeneralResponse(){
            return $this->generalResponse;
        }
    }
    
    class ManagePaymentResponse extends GeneralResponse {
        
        /**
         * @var         GeneralResponse                    An object, that represents the basic values from the response from mPAY24: 
         *                                                 status and return code
         */
        var $generalResponse;
        /**
         * @var         STRING                            The mPAY transaction ID
         */
        var $mpayTID;
        /**
         * @var         STRING                            The transaction ID of the shop
         */
        var $tid;
        
        /**
         * @abstract                                    Sets the values for a payment from the response from mPAY24: 
         *                                                 mPAY transaction IDand transaction ID from the shop
         * @param         STRING                            The SOAP response from mPAY24 (in XML form)
         */
        function ManagePaymentResponse($response) {
        	$this->generalResponse = new GeneralResponse($response);
        	
            if($response != '') {
            	$responseAsDOM = new DOMDocument();
	            $responseAsDOM->loadXML($response);
	            
	            if($responseAsDOM && $responseAsDOM->getElementsByTagName('mpayTID')->length != 0 && $responseAsDOM->getElementsByTagName('tid')->length != 0) {        
	                $this->mpayTID         = $responseAsDOM->getElementsByTagName('mpayTID')->item(0)->nodeValue;
	                $this->tid             = $responseAsDOM->getElementsByTagName('tid')->item(0)->nodeValue;
	            }
            } else {
                $this->generalResponse->setStatus("ERROR");
                $this->generalResponse->setReturnCode("The response is empty! Probably your request to mPAY24 was not sent! Please see your server log for more information!");
            }   
        }
        
        /**
         * @return        STRING                            The mPAY transaction ID, returned from mPAY24
         */
        public function getMpayTID() {
            return $this->mpayTID;
        }
        
        /**
         * @return        STRING                            The transaction ID of the shop, returned from mPAY24
         */
        public function getTid() {
            return $this->tid;
        }
        
        /**
         * @return        GeneralResponse                    The object, that contains the basic values from the response from mPAY24: 
         *                                                 status and return code
         */
        public function getGeneralResponse() {
            return $this->generalResponse;
        }
    }
    
    class ListPaymentMethodsResponse extends GeneralResponse {
        
        /**
         * @var         GeneralResponse                    An object, that represents the basic values from the response from mPAY24: 
         *                                                 status and return code
         */
        var $generalResponse;
        /**
         * @var         INTEGER                            The count of the payment methods, which are activated by mPAY24
         */
        var $all                    	= 0;
        /**
         * @var         ARRAY                            A list with the payment types, activated by mPAY24
         */
        var $pTypes                    = array();
        /**
         * @var         ARRAY                            A list with the brands, activated by mPAY24
         */
        var $brands                    = array();
        /**
         * @var         ARRAY                            A list with the descriptions of the payment methods, activated by mPAY24
         */
        var $descriptions            = array();
        
        /**
         * @abstract                                    Sets the values for a payment from the response from mPAY24: 
         *                                                 count, payment types, brands and descriptions
         * @param         STRING                            The SOAP response from mPAY24 (in XML form)
         */
        function ListPaymentMethodsResponse($response) {
        	$this->generalResponse = new GeneralResponse($response);
        	
            if($response != '') {
            	$responseAsDOM = new DOMDocument();
	            $responseAsDOM->loadXML($response);
	            
	            if($responseAsDOM && $responseAsDOM->getElementsByTagName('all')->length != 0) {        
	                $this->all             = $responseAsDOM->getElementsByTagName('all')->item(0)->nodeValue;
	                    
	                for($i = 0; $i < $this->all; $i++) {
	                    $this->pTypes[$i] = $responseAsDOM->getElementsByTagName('pType')->item($i)->nodeValue;
	                    $this->brands[$i] = $responseAsDOM->getElementsByTagName('brand')->item($i)->nodeValue;
	                    $this->descriptions[$i] = $responseAsDOM->getElementsByTagName('description')->item($i)->nodeValue;
	                }
	            }
            } else {
                $this->generalResponse->setStatus("ERROR");
                $this->generalResponse->setReturnCode("The response is empty! Probably your request to mPAY24 was not sent! Please see your server log for more information!");
            }   
        }
        
        /**
         * @return        INTEGER                            The count of the payment methods, returned from mPAY24
         */
        public function getAll() {
            return $this->all;
        }
        
        /**
         * @return        ARRAY                            The payment types, returned from mPAY24
         */
        public function getPTypes() {
            return $this->pTypes;
        }
        
        /**
         * @return        ARRAY                            The brands, returned from mPAY24
         */
        public function getBrands() {
            return $this->brands;
        }
        
        /**
         * @return        ARRAY                            The descriptions, returned from mPAY24
         */
        public function getDescriptions() {
            return $this->descriptions;
        }
        
        /**
         * @param        INTEGER                            The index of a payment type
         * @return        STRING                            A payment type, returned from mPAY24
         */
        public function getPType($i) {
            return $this->pTypes[$i];
        }
        
        /**
         * @param        INTEGER                            The index of a brand
         * @return        STRING                            A brand, returned from mPAY24
         */
        public function getBrand($i) {
            return $this->brands[$i];
        }
        
        /**
         * @param        INTEGER                            The index of a description
         * @return        STRING                            A description, returned from mPAY24
         */
        public function getDescription($i) {
            return $this->descriptions[$i];
        }
        
        /**
         * @return        GeneralResponse                    The object, that contains the basic values from the response from mPAY24: 
         *                                                 status and return code
         */
        public function getGeneralResponse() {
            return $this->generalResponse;
        }
    }
    
    class TransactionStatusResponse extends GeneralResponse {
        
        /**
         * @var         GeneralResponse                    An object, that represents the basic values from the response from mPAY24: 
         *                                                 status and return code
         */
        var $generalResponse;
        /**
         * @var         ARRAY                            A list with all the parameters for a transaction
         */
        var $params                    = array();
        /**
         * @var         INTEGER                            The count of all the paramerters for a transaction
         */
        var $paramCount                = 0;
        
        /**
         * @abstract                                    Sets the values for a transaction from the response from mPAY24: 
         *                                                 STATUS, PRICE, CURRENCY, LANGUAGE, etc
         * @param         STRING                            The SOAP response from mPAY24 (in XML form)
         */
        function TransactionStatusResponse($response) {
        	$this->generalResponse = new GeneralResponse($response);
            if($response != '') {
            	$responseAsDOM = new DOMDocument();
	            $responseAsDOM->loadXML($response);
	            
	            if($responseAsDOM && $responseAsDOM->getElementsByTagName('name')->length != 0) {        
	                $this->paramCount = $responseAsDOM->getElementsByTagName('name')->length;
	                $this->params['STATUS'] = $this->generalResponse->getStatus();
	                
	                for($i = 0; $i < $this->paramCount; $i++){
	                    if($responseAsDOM->getElementsByTagName("name")->item($i)->nodeValue == "STATUS")
	                        $this->params["TSTATUS"] = $responseAsDOM->getElementsByTagName("value")->item($i)->nodeValue;
	                    else
	                        $this->params[$responseAsDOM->getElementsByTagName('name')->item($i)->nodeValue]
	                                        = $responseAsDOM->getElementsByTagName('value')->item($i)->nodeValue;                    
	                }
	            }
            } else {
                $this->generalResponse->setStatus("ERROR");
                $this->generalResponse->setReturnCode("The response is empty! Probably your request to mPAY24 was not sent! Please see your server log for more information!");
            }   
        }
        
        /**
         * @return        INTEGER                            The count of all the paramerters for a transaction
         */
        public function getParamCount() {
            return $this->paramCount;
        }
        
        /**
         * @return        ARRAY                            The parameters for a transaction, returned from mPAY24
         */
        public function getParams() {
            return $this->params;
        }
        
        /**
         * @param        STRING                            The name of a parameter (for example: STATUS, PRICE, CURRENCY, etc)
         * @return        STRING                            A description, returned from mPAY24
         */
        public function getParam($i) {
        	if(isset($this->params[$i]))
                return $this->params[$i];
            else
                return false;
        }
        
        /**
         * @param        STRING                            The name of a parameter (for example: STATUS, PRICE, CURRENCY, etc)
         * @param        STRING                            The value of the parameter
         */
        public function setParam($name, $value) {
            $this->params[$name] = $value;
        }
        
        /**
         * @return        GeneralResponse                    The object, that contains the basic values from the response from mPAY24: 
         *                                                 status and return code
         */
        public function getGeneralResponse() {
            return $this->generalResponse;
        }
    }
?>