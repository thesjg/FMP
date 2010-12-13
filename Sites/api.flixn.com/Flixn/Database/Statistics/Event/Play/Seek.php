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

class FlixnDatabaseStatisticsEventPlaySeek extends FlixnDatabase {

    public function __construct()
    {
        $this->_tableName = 'event_play_seek';
        $this->_columns = array('id'            => ExDatabase::PARAM_INT,
                                'play_id'       => ExDatabase::PARAM_INT,
                                'offset_start'  => ExDatabase::PARAM_INT,
                                'offset_end'    => ExDatabase::PARAM_INT,
                                'timestamp'     => ExDatabase::PARAM_STR);
        $this->_columnData = array();
        
        parent::__construct(parent::DB_STATISTICS);
    }
}