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

class FlixnDatabaseStatisticsMedia extends FlixnDatabase {

    public function __construct()
    {
        $this->_tableName = 'media';
        $this->_columns = array('id'            => ExDatabase::PARAM_INT,
                                'user_id'       => ExDatabase::PARAM_INT
                                'media_id'      => ExDatabase::PARAM_STR);
        $this->_columnData = array();
        
        parent::__construct(parent::DB_STATISTICS);
    }
}