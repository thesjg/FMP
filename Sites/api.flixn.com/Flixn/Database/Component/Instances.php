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

class FlixnDatabaseComponentInstances extends FlixnDatabase {
    
    public function __construct() {
        $this->_tableName = 'component_instances';
        $this->_columns = array('id'               => ExDatabase::PARAM_INT,
                                'user_id'          => ExDatabase::PARAM_INT,
                                'type_id'          => ExDatabase::PARAM_INT,
                                'component_id'     => ExDatabase::PARAM_STR,
                                'component_key'    => ExDatabase::PARAM_STR,
                                'name'             => ExDatabase::PARAM_STR,
                                'restrict_domains' => ExDatabase::PARAM_BOOL,
                                'created'          => ExDatabase::PARAM_STR);
        $this->_columnData = array();
        
        parent::__construct();
    }
    
    public function loadByComponentId($component_id) {
        return $this->loadBy('component_id', $component_id);
    }
    
    public function loadByComponentKey($component_key) {
        return $this->loadBy('component_key', $component_key);
    }
}