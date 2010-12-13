<?php
/**
 * Exhibition
 *
 * @category    Exhibition
 * @package     Network
 * 
 * @author      Samuel J. Greear
 * @copyright   Copyright (c) 2005-2006 Samuel J. Greear
 * @copyright   Copyright (c) 2007-2008 Evilcode Corporation
 * @version     $Id $
 */

class NetworkCurl {

    public $result;
    public $vars = '';
    public $tempFile;
    public $requestType;
    public $url;

    public $Handle;

    public function __construct($request_type='POST') {

        $this->handle = curl_init();

        $this->requestType = $request_type;
        if ($request_type == 'POST')
            curl_setopt($this->handle, CURLOPT_POST, 1);

        curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, 1);
    }

    public function createCookie() {
        $this->tempFile = tempnam("/tmp", "CurlCookie");
        curl_setopt($this->handle, CURLOPT_COOKIEJAR, $this->tempFile);
    }

    public function useCookie($filename) {
        curl_setopt($this->handle, CURLOPT_COOKIEFILE, $filename);
    }

    public function setURL($url) {
        $this->url = $url;
        curl_setopt($this->handle, CURLOPT_URL, $url);
    }

    public function addVar($key, $value) {
        $this->vars .= $key . "=" . urlencode($value) . "&";
    }

    public function addVars($arr) {
        foreach ($arr as $key => $value)  {
            $this->addVar($key, $value);
        }
    }

    public function execute() {
        $this->vars = substr($this->vars, 0, -1);

        if ($this->requestType == 'POST') {
            curl_setopt($this->handle, CURLOPT_POSTFIELDS, $this->vars);
        } else {
            $this->url = $this->url . '?' . $this->vars;
            curl_setopt($this->handle, CURLOPT_URL, $this->url);
        }

        $this->result = curl_exec($this->handle);
        curl_close($this->handle);
        return $this->result;
    }
}