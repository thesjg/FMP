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

class FlixnDatabaseMediaMetadata extends FlixnDatabase {
    
    public function __construct() {
        $this->_tableName = 'media_metadata';
        $this->_columns = array('id'        => ExDatabase::PARAM_INT,
                                'media_id'  => ExDatabase::PARAM_INT,
                                'key'       => ExDatabase::PARAM_STR,
                                'value'     => ExDatabase::PARAM_STR);
        $this->_columnData = array();
        
        parent::__construct();
    }
    
    public function loadByKey($key) {
        return $this->loadBy('key', $key);
    }
}