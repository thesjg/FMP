<?php
/**
 * Flixn
 *
 * @category    Flixn
 * @package     Database
 * 
 * @author      Samuel J. Greear
 * @copyright   Copyright (c) 2008 Flixn, Inc.
 * @version     $Id $
 */

class FlixnDatabaseAPIUsersAuthenticated extends FlixnDatabase {
    
    public function __construct() {
        $this->_tableName = 'api_users_authenticated';
        $this->_columns = array('id'            => ExDatabase::PARAM_INT,
                                'user_id'       => ExDatabase::PARAM_INT,
                                'session_id'    => ExDatabase::PARAM_INT,
                                'authenticated' => ExDatabase::PARAM_STR,
                                'used'          => ExDatabase::PARAM_STR);
        $this->_columnData = array();
        
        parent::__construct();
    }
    
    public function loadBySessionId($sessionId) {
        return $this->loadBy('session_id', $sessionId);
    }
}