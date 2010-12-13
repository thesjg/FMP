<?php
/**
 * Exhibition
 *
 * @category    Exhibition
 * @package     Services
 * 
 * @author      Samuel J. Greear
 * @copyright   Copyright (c) 2008 Evilcode Corporation
 * @version     $Id $
 */

/**
 * ExServices
 *
 * TODO: This needs MUCH better get var parsing / error detection
 */
class ExServices {
    
    const SOAP = 'soap';
    const REST = 'rest';
    
    public $requestType;
    public $requestVersion;
    public $requestService;
    
    public $requestClass;
    public $requestMethod;
    public $requestVariables;
    
    private $_requestURI;
    private $_classPrefix;
    private $_headerPrefix;
    
    public function __construct() {
        $this->requestVariables = array();
        
        $this->_classPrefix = 'FlixnServices';
        $this->_headerPrefix = 'X-Flixn-';
        
//        try {
            $this->parseRequest();
            $this->validateRequest();        
    }
    
    private function parseRequest() {
        $request = explode('?', urldecode($_SERVER['REQUEST_URI']));
        if (count($request) > 1)
            $this->_requestURI = array_shift($request);
        else
            $this->_requestURI = urldecode($_SERVER['REQUEST_URI']);
        
        $s_uri = explode('/', $this->_requestURI);
        /* /services/version/type/method will result in a blank 0-indexed
          element, this is to neuter it. */
        array_shift($s_uri);
        
        if (count($s_uri) < 3)
            throw new ExServicesException(ExServicesErrors::INVALID_REQUEST);

        $this->requestType = strtolower($s_uri[0]);
        $this->requestVersion = $s_uri[1];       
        $this->requestService = strtolower($s_uri[2]);

        $method = $s_uri[3];
        preg_match('/[A-Z]/', $method, $matches, PREG_OFFSET_CAPTURE);
        if (count($matches) < 1)
            throw new ExServicesException(ExServicesErrors::UNKNOWN_METHOD);

        $this->requestClass = $this->_classPrefix . ucfirst(substr($method, 0, $matches[0][1]));
        $this->requestMethod = strtolower($matches[0][0]) . substr($method, $matches[0][1]+1);
        
        $prefix = 'HTTP_' . strtoupper(str_replace('-', '_', $this->_headerPrefix));
        foreach ($_SERVER as $key => $value)
            if (strncmp($prefix, $key, strlen($prefix)) == 0) {
                $this->requestVariables[strtolower(substr($key, strlen($prefix)))] = $value;
            }
            
        $request_vars = explode('&', array_shift($request));
        if (count($request_vars) > 1) {
            foreach ($request_vars as $var) {
                list($key, $value) = explode('=', $var);
                if ($value) {
                    $this->requestVariables[strtolower($key)] = $value;
                }
            }
        }
    }
    
    private function validateRequest() {
        if ($this->requestType !== 'services')
            throw new ExServicesException(ExServicesErrors::INVALID_REQUEST);

        if (!is_numeric($this->requestVersion))
            throw new ExServicesException(ExServicesErrors::UNSUPPORTED_API_VERSION);

        $service_types = array(self::SOAP, self::REST);
        if (!in_array(strtolower($this->requestService), $service_types))
            throw new ExServicesException(ExServicesErrors::INVALID_SERVICE_TYPE);
    }
}