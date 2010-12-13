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

class FlixnDatabaseStatisticsComponentInstances extends FlixnDatabase {

    public function __construct()
    {
        $this->_tableName = 'component_instances';
        $this->_columns = array('id'            => ExDatabase::PARAM_INT,
                                'user_id'       => ExDatabase::PARAM_INT,
                                'type_id'       => ExDatabase::PARAM_INT,
                                'component_id'  => ExDatabase::PARAM_STR);
        $this->_columnData = array();
        
        parent::__construct(parent::DB_STATISTICS);
    }

    public function loadByComponentId($componentId)
    {
        return $this->loadBy('component_id', $componentId);
    }
}