<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
*/

/**
 * Description of REST_Request
 *
 * @author BetoR
 */
class Model3_Rest_ApiClient
{
    protected $_accepted;
    protected $_apiKey;
    protected $_url;
    protected $_lastError;
    protected $_lastStatus;

    public function  __construct( $apiKey, $url )
    {
        $this->_apiKey = $apiKey;
        $this->_url = $url;
        $result = $this->curlExecute( 'Validate', array(), array(), 'GET' );
//        $result = 'valid';
        if( $result === false )
        {
            throw new Exception( 'Error while validating api key: Can\'t do curl' );
        }
        else if( $result != 'valid' )
        {
            throw new Exception( 'Error while validating api key: '.$result );
        }
    }

    public function  __call( $name,  $arguments)
    {
        if( strpos($name, 'do' ) === 0 && count( $arguments ) >=4 )
        {
            $controller = $arguments[0];
            $method = strtoupper(substr( $name,2 ));
            $urlParams = $arguments[1];
            if( !is_array( $urlParams ) )
            {
                $urlParams = array( $urlParams );
            }
            $fields = $arguments[2];
            $responseFormat = $arguments[3];
            if( is_array( $fields ))
            {
                if( $method == 'GET')
                {
                    array_splice( $urlParams, count($urlParams), 0, $fields );
                }
                $res = $this->curlExecute( $controller, $urlParams, $fields, $method, $responseFormat );
                return $res;
            }
        }
        return false;
    }

    private function curlExecute( $controller, $urlParams, $fields, $method, $responseFormat='xml' )
    {
        $fields = (is_array($fields)) ? http_build_query($fields, '', '&') : $fields;
        if( $urlParams != null && !is_array($urlParams))
        {
            $urlParams = array( $urlParams );
        }
        $urlParams = (is_array($urlParams)) ? implode( $urlParams,'/' ) : '';

        $url = $this->_url;
        $url .= $controller.'/';
        $url .= $this->_apiKey.'/';
        $url .= $urlParams;
        $ch = curl_init($url);
        if($ch)
        {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method );
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Length: ' . strlen($fields)));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            curl_setopt($ch, CURLOPT_HTTPHEADER,array ( 'Accept: application/'.$responseFormat ));
            $result = curl_exec($ch);
            //var_dump($result);
            $this->_lastStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            //var_dump($this->_lastStatus);
            if ( $this->_lastStatus == 200 && $result != 'valid' )
            {
                $ret = @simplexml_load_string($result);
                if($ret === FALSE)
                {
                    $this->_lastError = 'Can\'t create xml object';
                }
            }
            else if($result == 'valid')
            {
                $ret = $result;
            }
            else
            {
                $this->_lastError = $result;
                $ret = FALSE;
            }
        }
        else
        {
            $ret = false;
        }
        return $ret;
    }

    public function convertToArray( SimpleXMLElement $xmlElement )
    {
        $ret = array();
        foreach($xmlElement->children() as $key=>$value)
        {
            if(is_object($value))
            {
                if($value->count() > 0)
                {
                    $ret[$key] = $this->convertToArray($value);
                }
                else
                {
                    $ret[$key] = $value->__toString();
                }
            }
            else
            {
                $ret[$key] = $value;
            }
        }
        return $ret;
    }

    public function getLastError()
    {
        return $this->_lastError;
    }

    public function getLastStatus()
    {
        return $this->_lastStatus;
    }
}
?>
