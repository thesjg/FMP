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

class FlixnDatabaseStatisticsEventPlayPause extends FlixnDatabase {

    public function __construct()
    {
        $this->_tableName = 'event_play_pause';
        $this->_columns = array('id'            => ExDatabase::PARAM_INT,
                                'play_id'       => ExDatabase::PARAM_INT,
                                'offset'        => ExDatabase::PARAM_INT,
                                'timestamp'     => ExDatabase::PARAM_STR);
        $this->_columnData = array();
        
        parent::__construct(parent::DB_STATISTICS);
    }
}