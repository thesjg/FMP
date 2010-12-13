<?php
/**
 * Flixn
 *
 * @category    Flixn
 * @package     Base
 * 
 * @author      Samuel J. Greear
 * @copyright   Copyright (c) 2006-2008 Flixn, Inc.
 * @version     $Id $
 */

/**
 * FlixnSession
 * 
 * @todo Validate session by IP (CIDR/16?)
 * @todo Move settings to global configuration
 * 
 * XXX: Remove hardcoded cluster id
 * XXX: Remove hardcoded cookieDomain
 */
class FlixnSession {

    public $cookieName;
    public $cookiePath;
    public $cookieDomain;

    private static $_sessionId = NULL;
    private static $_sessionData = NULL;

    public function __construct($sessionId=NULL) {
        $this->cookieName = 'flixn_sid';
        $this->cookiePath = '/';
        $this->cookieDomain = '.flixn.com';

        if (self::$_sessionId !== NULL)
            return true;

        if ($sessionId !== NULL) {
            $fds = new FlixnDatabaseSession();
            if ($fds->loadBySessionId($sessionId)) {
                self::$_sessionData = $fds;
                self::$_sessionId = $sessionId;
                return true;
            } else {
                return false;
            }
        }
        
        $sessionId = $this->generateSid();
//            $this->setCookie($sessionId);

        $fds = new FlixnDatabaseSession();
        $fds->session_id = $sessionId;
        $fds->ip_address = $_SERVER['REMOTE_ADDR'];
        $fds->save();
            
        $fds = new FlixnDatabaseSession();
        $fds->loadBySessionId($sessionId);

        self::$_sessionData = $fds;
        self::$_sessionId = $sessionId;
        
        return true;
    }
    
    public static function getSessionId() {
        return self::$_sessionId;
    }

    public function authenticate($apiKey) {
        $fdu = new FlixnDatabaseUsers();
        $fdu->loadByUUID($apiKey);
        if (isset($fdu->id)) {

            try {
                $fdaua = new FlixnDatabaseAPIUsersAuthenticated();
                $fdaua->user_id = $fdu->id;
                $fdaua->session_id = self::$_sessionData->id;
                $fdaua->save();
            } catch (Exception $e) {
                $fdaua = new FlixnDatabaseAPIUsersAuthenticated();
                if (!$fdaua->loadBySessionId(self::$_sessionData->id))
                    return false;
                
                $fdaua->authenticated = 'NOW()';
                $fdaua->used = 'NOW()';
                $fdaua->save();
            }
            
            return true;
        }
        
        $fdci = new FlixnDatabaseComponentInstances();
        $fdci->loadByComponentKey($apiKey);

        if (isset($fdci->id)) {
            
            try {
                $fdaca = new FlixnDatabaseAPIComponentsAuthenticated();
                $fdaca->component_instance_id = $fdci->id;
                $fdaca->session_id = self::$_sessionData->id;
                $fdaca->save();
            } catch (Exception $e) {
                $fdaca = new FlixnDatabaseAPIComponentsAuthenticated();
                if (!$fdaca->loadByComponentInstanceIdAndSessionId($fdci->component_instance_id, self::$_sessionData->id))
                    return false;
                
                $fdaca->authenticated = 'NOW()';
                $fdaca->save();
            }

            return true;
        }
        
        return false;
    }

    private function setCookie($value) {
        setcookie($this->cookieName, $value, PHP_INT_MAX, $this->cookiePath, $this->cookieDomain, false);
    }

    private function getCookie() {
        if (isset($_COOKIE[$this->cookieName]))
            return $_COOKIE[$this->cookieName];

        return NULL;
    }

    private function generateSid() {
        /* XXX */
        $cluster_id = 1;
    
        $ident = new FlixnIdentification();
        return $ident->UUIDSessionGenerate($cluster_id);
    }
}