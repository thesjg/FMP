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

class FlixnDatabaseStatisticsComponentTypes extends FlixnDatabase {

    public function __construct()
    {
        $this->_tableName = 'component_types';
        $this->_columns = array('id'            => ExDatabase::PARAM_INT,
                                'name'          => ExDatabase::PARAM_STR,
                                'version'       => ExDatabase::PARAM_STR);
        $this->_columnData = array();
        
        parent::__construct(parent::DB_STATISTICS);
    }
}