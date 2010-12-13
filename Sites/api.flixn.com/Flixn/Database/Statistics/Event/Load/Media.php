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

class FlixnDatabaseStatisticsEventLoadMedia extends FlixnDatabase {

    public function __construct()
    {
        $this->_tableName = 'event_load_media';
        $this->_columns = array('id'            => ExDatabase::PARAM_INT,
                                'load_id'       => ExDatabase::PARAM_INT,
                                'media_id'      => ExDatabase::PARAM_INT,
                                'load_time'     => ExDatabase::PARAM_INT,
                                'timestamp'     => ExDatabase::PARAM_STR);
        $this->_columnData = array();
        
        parent::__construct(parent::DB_STATISTICS);
    }
}