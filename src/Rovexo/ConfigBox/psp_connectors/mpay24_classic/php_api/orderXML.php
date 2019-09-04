<?php
	/**
	 * @author				support@mpay24.com
	 * @version             $Id: orderXML.php 5217 2012-10-16 05:27:43Z anna $
	 * @filesource			orderXML.php
	 * @license             http://ec.europa.eu/idabc/eupl.html EUPL, Version 1.1
	 */

/**
 * @abstract        STRING MDXI_SCHEMA = the specified from mPAY24 URL, where you can download the MDXI schema
 */
define ("MDXI_SCHEMA","https://www.mpay24.com/schemas/MDXI/v3.0/MDXI.xsd");

/**
 * @property object $Order
 */
class ORDER {
    private $doc;
    private $node;

    /**
     * @abstract                                        Create a DOMDocument or a ORDER-Object with root $doc
     * @param            DOMNode                         The root DOMNode of an XML tree
     * @param            DOMNode                         The child DOMNode
     */
    public function ORDER($doc=null,$node=null) {
        if ($doc)
        	$this->doc = $doc;
        else {
            $this->doc = new DOMDocument("1.0", "UTF-8");
            $this->doc->formatOutput = true;
        }
        
        if ($node)
        	$this->node = $node;
        else
        	$this->node = $this->doc;
    }

    /**
     * @abstract                                        Generic call-Method instead of numerous setter methods
     * @param             STRING                             The name of the method, which is called for the Item-Object
     * @param             ARRAY                             The arguments with them the method is called - minOccurance = 0, maxOccurance = 2:
     *
     *                                                     The first argument must be a positive integer (will be used as a index)
     *
     *                                                     The second argument is optional and would be used as value for the DOMNode
     */
    public function __call($method, $args) {
        if(substr($method, 0, 3) == "set" && $args[0] != '') {
            $attributeName = substr($method, 3);
            
            $value = $args[0];
            
            if(preg_match('/[0-9]+,[0-9]+/', $value, $match))
                   $value = str_replace(',', '.', $match[0]);

            if(preg_match('/[0-9]+.[0-9]+/', $value, $match) && $value == $match[0] && $attributeName != 'shippingCosts' && (is_int(strpos($attributeName, 'price')) || is_int(strpos($attributeName, 'Price')) || is_int(strpos($attributeName, 'Tax')) || is_int(strpos($attributeName, 'cost')) || is_int(strpos($attributeName, 'Cost'))))
              $value = number_format(floatval($match[0]), 2, '.', '');
          
            $this->node->setAttribute($attributeName, $value);
        } elseif($args[0] != '') {
            if(sizeof($args)>2)
              die("It is not allowed to set more than 2 arguments for the node '$method'!");
            if(!is_int($args[0]) || $args[0] < 1)
              die("The first argument for the node '$method' must be whole number, bigger than 0!");

            $name = $method . '[' . $args[0] . ']';

            $xpath = new DOMXPath($this->doc);
            $qry = $xpath->query($name,$this->node);

            if ($qry->length > 0)
                return new ORDER($this->doc,$qry->item(0));
            else {
                if(array_key_exists(1, $args)) {
                  $value = $args[1];
                  
                  if(preg_match('/[0-9]+,[0-9]+/', $value, $match))
                   $value = str_replace(',', '.', $match[0]);
                  
                  if(preg_match('/[0-9]+.[0-9]+/', $value, $match) && $value == $match[0] && $name != 'shippingCosts' && (is_int(strpos($name, 'price')) || is_int(strpos($name, 'Price')) || is_int(strpos($name, 'Tax')) || is_int(strpos($name, 'cost')) || is_int(strpos($name, 'Cost'))))
                    $value = number_format(floatval($match[0]), 2, '.', '');
                  
                  $node = $this->doc->createElement($method, $value);
                }
                else
                  $node = $this->doc->createElement($method);
                
                $node = $this->node->appendChild($node);
                return new ORDER($this->doc,$node);
            }
        }
    }

    /**
     * @abstract                                        Get the value of a ORDER-Variable
     * @param             STRING                             The name of the Node you need
     * @return            ORDER                             The ORDER-Object of a DOMDocument tree
     */
    public function __get($name) {
        $xpath = new DOMXPath($this->doc);
        $qry = $xpath->query($name,$this->node);

        if ($qry->length > 0)
            return new ORDER($this->doc,$qry->item(0));
        else {
            $node = $this->doc->createElement($name);
            $node = $this->node->appendChild($node);
            return new ORDER($this->doc,$node);
        }
    }

    /**
     * @abstract                                        Set the value of a ORDER-Variable
     * @param             STRING                             The name of the Node you want to set
     * @param             ANY                                 The value of the Node you want to set
     */
    public function __set($name, $value) {
        $xpath = new DOMXPath($this->doc);
        $qry = $xpath->query($name,$this->node);

        $value = str_replace('&', '&amp;', $value);
        
        if(preg_match('/[0-9]+,[0-9]+/', $value, $match))
          $value = str_replace(',', '.', $match[0]);
        
        if(preg_match('/[0-9]+.[0-9]+/', $value, $match) && $value == $match[0] && $name != 'shippingCosts' && (is_int(strpos($name, 'price')) || is_int(strpos($name, 'Price')) || is_int(strpos($name, 'Tax')) || is_int(strpos($name, 'cost')) || is_int(strpos($name, 'Cost'))))
          $value = number_format(floatval($match[0]), 2, '.', '');

        if(strpos($value, "<") || strpos($value, ">"))
            $value = "<![CDATA[" . $this->xmlencode($value) . "]]>";
        
        if ($qry->length > 0)
            $qry->item(0)->nodeValue = $value;
        else {
            $node = $this->doc->createElement($name,$value);
            $this->node = $this->node->appendChild($node);
        }
    }

    /**
     * @abstract                                        Create a XML-Object from the ORDER-Object
     * @return            XML                             The created XML from the ORDER
     */
    public function toXML() {
        return $this->doc->saveXML();
    }

    /**
     * @abstract                                        Validate the ORDER with the schema, defined in the constant MDXI_SCHEMA
     * @uses            CONSTANT                         MDXI_SCHEMA
     * @return            BOOLEAN                         TRUE if the validation was successful or FALSE
     */
    public function validate() {
      $mdxi = "/MDXI.xsd";
      
      if($this->olderThanOneWeek(__DIR__ . $mdxi)){
        set_time_limit(0);
        ini_set('display_errors',true);
        
        $fp = fopen (__DIR__ . '/MDXInew.xsd', 'w');
        $ch = curl_init(MDXI_SCHEMA);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_CAINFO, __DIR__.'/cacert.pem');
        $result = curl_exec($ch);
        
        if(curl_getinfo($ch, CURLINFO_HTTP_CODE) === 200 && file_exists(__DIR__ . '/MDXInew.xsd')) {
          unlink(__DIR__ . $mdxi);
          fclose($fp);
          rename(__DIR__ . "/MDXInew.xsd", __DIR__ . $mdxi);
        } else
          fclose($fp);
        
        curl_close($ch);
      }

      return $this->doc->schemaValidate(__DIR__ . $mdxi);
    }
    
    private function xmlencode($txt)
    {
        $txt = str_replace('<', '&lt;', $txt);
        $txt = str_replace('>', '&gt;', $txt);
        $txt = str_replace('&amp;apos;', "'", $txt);
        $txt = str_replace('&amp;quot;', '"', $txt);
        return $txt;
    }
    
    private function olderThanOneWeek($filename){
      $year = date ("Y", filemtime($filename));
      $month = date ("m", filemtime($filename));
      $day = date ("d", filemtime($filename));
      
      $tyear = date ("Y");
      $tmonth = date ("m");
      $tday = date ("d");
      
      if($tyear > $year)
        return true;
      else {
        if($tmonth > $month){
          if($tday > 7)
            return true;
          else {
            if($tday-7+30 < $day)
              return false;
            else
              return true;
          }
        } else {
          if($tday-7 > $day)
            return true;
          else {
            return false;
          }
        }
      }
    }
}
?>