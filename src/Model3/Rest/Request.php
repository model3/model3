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
class Model3_Rest_Request extends Model3_Request
{
    protected $_method;
    protected $_accepted;

    public function __construct()
    {
        $path = array_keys($_GET);
        $this->_controller = 'Index';
        $this->_action = 'index';
        $this->_params = array();
        $this->_method = strtolower($_SERVER['REQUEST_METHOD']);
        $this->_accepted = isset($_SERVER['HTTP_ACCEPT'])?$_SERVER['HTTP_ACCEPT']:'xml';
        //(strpos($_SERVER['HTTP_ACCEPT'], 'json')) ? 'json' : 'xml';

        if(isset($path[0]))
        {
            $route= $path[0];

            // Se debe limpiar de caracteres no validos...
            $route= rtrim($path[0], '/');
            $routeParts = preg_split('/\//',$route);

            $idx = 0;
            $this->_component = null;
            $componentes = Model3_Site::getComponentList();
            if(in_array($routeParts[$idx], $componentes))
            {
                $this->_component = $routeParts[$idx];
                $idx++;
            }

            $this->_module = null;
            if(isset($routeParts[$idx]))
            {
                $modulos = Model3_Site::getModuleList();
                if(in_array($routeParts[$idx], $modulos))
                {
                    $this->_module = $routeParts[$idx];
                    $idx++;
                }
            }

            $this->_controller = (isset($routeParts[$idx])&&!empty($routeParts[$idx]))?$routeParts[$idx]:"Index";
            $idx++;
            $this->_action = (isset($routeParts[$idx])&&!empty($routeParts[$idx]))?$routeParts[$idx]:"index";
            $idx++;

            for($cont = 0;$cont < $idx; $cont++)
            {
                array_shift($routeParts);
            }
            $this->_params=$routeParts;
        }
    }
    public function setMethod($method)
    {
        $this->_method = $method;
    }

    public function getMethod()
    {
        return $this->_method;
    }

    public function getApiKey()
    {
        return $this->_action;
    }

    public function getHttpAccept()
    {
        return $this->_accepted;
    }
    public function sendResponse($status = 200, $body = '', $content_type = 'text/html')
    {
        $status_header = 'HTTP/1.1 ' . $status . ' ' . self::getStatusCodeMessage($status);
        // set the status
        header($status_header);
        // set the content type
        header('Content-type: ' . $content_type);

        // pages with body are easy
        if($body != '')
        {
            // send the body
            echo $body;
            exit;
        }
        // we need to create the body if none is passed
        else
        {
            // create some body messages
            $message = '';

            // this is purely optional, but makes the pages a little nicer to read
            // for your users.  Since you won't likely send a lot of different status codes,
            // this also shouldn't be too ponderous to maintain
            switch($status)
            {
                case 401:
                    $message = 'You must be authorized to view this page.';
                    break;
                case 404:
                    $message = 'The requested URL ' . $_SERVER['REQUEST_URI'] . ' was not found.';
                    break;
                case 500:
                    $message = 'The server encountered an error processing your request.';
                    break;
                case 501:
                    $message = 'The requested method is not implemented.';
                    break;
            }

            // servers don't always have a signature turned on (this is an apache directive "ServerSignature On")
            $signature = ($_SERVER['SERVER_SIGNATURE'] == '') ? $_SERVER['SERVER_SOFTWARE'] . ' Server at ' . $_SERVER['SERVER_NAME'] . ' Port ' . $_SERVER['SERVER_PORT'] : $_SERVER['SERVER_SIGNATURE'];

            // this should be templatized in a real-world solution
            $body = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
						<html>
							<head>
								<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
								<title>' . $status . ' ' . self::getStatusCodeMessage($status) . '</title>
							</head>
							<body>
								<h1>' . self::getStatusCodeMessage($status) . '</h1>
								<p>' . $message . '</p>
								<hr />
								<address>' . $signature . '</address>
							</body>
						</html>';

            echo $body;
            exit;
        }
    }

    public static function getStatusCodeMessage($status)
    {
        // these could be stored in a .ini file and loaded
        // via parse_ini_file()... however, this will suffice
        // for an example
        $codes = Array(
                100 => 'Continue',
                101 => 'Switching Protocols',
                200 => 'OK',
                201 => 'Created',
                202 => 'Accepted',
                203 => 'Non-Authoritative Information',
                204 => 'No Content',
                205 => 'Reset Content',
                206 => 'Partial Content',
                300 => 'Multiple Choices',
                301 => 'Moved Permanently',
                302 => 'Found',
                303 => 'See Other',
                304 => 'Not Modified',
                305 => 'Use Proxy',
                306 => '(Unused)',
                307 => 'Temporary Redirect',
                400 => 'Bad Request',
                401 => 'Unauthorized',
                402 => 'Payment Required',
                403 => 'Forbidden',
                404 => 'Not Found',
                405 => 'Method Not Allowed',
                406 => 'Not Acceptable',
                407 => 'Proxy Authentication Required',
                408 => 'Request Timeout',
                409 => 'Conflict',
                410 => 'Gone',
                411 => 'Length Required',
                412 => 'Precondition Failed',
                413 => 'Request Entity Too Large',
                414 => 'Request-URI Too Long',
                415 => 'Unsupported Media Type',
                416 => 'Requested Range Not Satisfiable',
                417 => 'Expectation Failed',
                500 => 'Internal Server Error',
                501 => 'Not Implemented',
                502 => 'Bad Gateway',
                503 => 'Service Unavailable',
                504 => 'Gateway Timeout',
                505 => 'HTTP Version Not Supported'
        );

        return (isset($codes[$status])) ? $codes[$status] : '';
    }
}
?>
