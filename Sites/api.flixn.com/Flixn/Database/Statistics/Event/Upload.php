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

class FlixnDatabaseStatisticsEventUpload extends FlixnDatabase {

    public function __construct()
    {
        $this->_tableName = 'event_upload';
        $this->_columns = array('id'            => ExDatabase::PARAM_INT,
                                'load_id'       => ExDatabase::PARAM_INT,
                                'file_size'     => ExDatabase::PARAM_INT,
                                'timestamp_start' => ExDatabase::PARAM_STR,
                                'timestamp_end' => ExDatabase::PARAM_STR);
        $this->_columnData = array();
        
        parent::__construct(parent::DB_STATISTICS);
    }
}