<?php
/**
 * Flixn
 *
 * @category    Flixn
 * @package     Services
 * 
 * @author      Samuel J. Greear
 * @copyright   Copyright (c) 2008 Flixn, Inc.
 * @version     $Id $
 */

class FlixnServicesSession extends FlixnServices {

    public function __construct() {
    }

    /**
     * (re-)Initiate an API session
     * @access public
     * 
     * @param string $sessionId Current session identifier, if available
     * @return string sessionid
     */
    public function initiate($sessionId=NULL) {
        $s = new FlixnSession($sessionId);
        return $s->getSessionId();
    }
    
    /**
     * Opens up restricted API methods
     * @access public
     *
     * @param string $sessionId Current session identifier
     * @param string $apiKey API Key to authorize
     * @return boolean authenticated
     */
    public function authenticate($sessionId, $apiKey) {
        $s = new FlixnSession($sessionId);
        return $s->authenticate($apiKey);
    }
}