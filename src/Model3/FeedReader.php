<?php
class Model3_FeedReader
{
	// Variables para curl
	private $_proxyServer;
	private $_proxyPort;
	
	public function __construct()
	{
		$this->_proxyServer = NULL;
		$this->_proxyPort = NULL;
	}
	
	public function getTwitterRss($username,$count = 10)
	{
		// Forma la url para twitter
		$urlTwitter = 'http://twitter.com/statuses/user_timeline/'.$username.'.xml?count='.$count;
		// Llama getRSS
		$xml = $this->getRss($urlTwitter);
		return $xml;
	}

    public function validaTwitterRss($username)
	{
		// Forma la url para twitter
		$urlTwitter = 'http://twitter.com/statuses/user_timeline/'.$username.'.xml';
		// Llama getRSS
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($this->getRss($urlTwitter));
        if($xml->error)
           return false;
        else
           return true;
	}

    public function validaRss($url)
    {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($this->getRss($url));
        
        if($xml)
        {
            if($xml->error)
               return false;
            else
               return true;
        }
        else
            return false;
    }

	public function getRSS($url)
	{
		// Llamdo a curl, regresa XML        
		$curl_handle=curl_init();
		curl_setopt($curl_handle,CURLOPT_URL,$url);
		if($this->_proxyServer != NULL)
		{
            curl_setopt($curl_handle, CURLOPT_PROXY, $this->_proxyServer.":".$this->_proxyPort);
			curl_setopt($curl_handle, CURLOPT_PROXYPORT, $this->_proxyPort); 
		}
        curl_setopt($curl_handle, CURLOPT_HEADER, 0);
        curl_setopt($curl_handle, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_handle, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.3) Gecko/20070309 Firefox/2.0.0.3");
        //curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT,2);        
		$buffer = curl_exec($curl_handle);
		curl_close($curl_handle);
       // var_dump($buffer);
		return $buffer;
	}
	
	public function setProxy($server, $port)
	{
		$this->_proxyServer = $server;
		$this->_proxyPort = $port;
	}
}