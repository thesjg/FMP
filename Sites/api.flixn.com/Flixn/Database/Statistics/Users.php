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

class FlixnDatabaseStatisticsUsers extends FlixnDatabase {

    public function __construct()
    {
        $this->_tableName = 'users';
        $this->_columns = array('id'            => ExDatabase::PARAM_INT,
                                'uuid'          => ExDatabase::PARAM_STR,
                                'username'      => ExDatabase::PARAM_STR);
        $this->_columnData = array();
        
        parent::__construct(parent::DB_STATISTICS);
    }
    
    public function loadByUUID($uuid)
    {
        return $this->loadBy('uuid', $uuid);
    }
}