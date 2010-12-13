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

class FlixnDatabaseComponentUploaderSettings extends FlixnDatabase {
    
    public function __construct() {
        $this->_tableName = 'component_uploader_settings';
        $this->_columns = array('id'                    => ExDatabase::PARAM_INT,
                                'instance_id'           => ExDatabase::PARAM_INT,
                                'single'                => ExDatabase::PARAM_BOOL,
                                'size_limit'            => ExDatabase::PARAM_INT,
                                'file_limit'            => ExDatabase::PARAM_INT);
        $this->_columnData = array();
        
        parent::__construct();
    }
    
    public function loadByInstanceId($instanceId) {
        return $this->loadBy('instance_id', $instanceId);
    }
}