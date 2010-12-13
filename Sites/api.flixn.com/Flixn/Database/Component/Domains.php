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

class FlixnDatabaseComponentDomains extends FlixnDatabase {
    
    public function __construct() {
        $this->_tableName = 'component_domains';
        $this->_columns = array('id'               => ExDatabase::PARAM_INT,
                                'instance_id'      => ExDatabase::PARAM_STR,
                                'domain'           => ExDatabase::PARAM_STR);
        $this->_columnData = array();
        
        parent::__construct();
    }
    
    public function loadAllByInstanceId($instance_id) {
        $columns = array();
        foreach ($this->_columns as $key => $value)
            if ($key != 'instance_id')
                $columns[] = $key;
            
        $cols = implode(', ', $columns);
        $query = 'SELECT ' . $cols . ' FROM ' . $this->_tableName
               . " WHERE instance_id='" . $instance_id . "'";
               
        if ($this->printQueries)
            print $query;
               
        return $this->query($query);
    }
}