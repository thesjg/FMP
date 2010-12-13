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

class FlixnDatabaseAPIComponentsAuthenticated extends FlixnDatabase {
    
    public function __construct() {
        $this->_tableName = 'api_components_authenticated';
        $this->_columns = array('id'                    => ExDatabase::PARAM_INT,
                                'component_instance_id' => ExDatabase::PARAM_INT,
                                'session_id'            => ExDatabase::PARAM_INT,
                                'authenticated'         => ExDatabase::PARAM_STR);
        $this->_columnData = array();
        
        parent::__construct();
    }
    
    public function loadByComponentInstanceIdAndSessionId($componentInstanceId, $sessionId) {
        return $this->loadByMany(array('component_instance_id' => $componentInstanceId,
                                       'session_id'            => $sessionId));
    }
}