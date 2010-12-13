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

class FlixnDatabaseStatisticsEventLoad extends FlixnDatabase {

    public function __construct()
    {
        $this->_tableName = 'event_load';
        $this->_columns = array('id'            => ExDatabase::PARAM_INT,
                                'component_instance_id' => ExDatabase::PARAM_INT,
                                'url_id'        => ExDatabase::PARAM_INT,
                                'timestamp'     => ExDatabase::PARAM_STR);
        $this->_columnData = array();
        
        parent::__construct(parent::DB_STATISTICS);
    }
}