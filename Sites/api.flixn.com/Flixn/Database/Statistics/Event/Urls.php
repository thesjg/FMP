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

class FlixnDatabaseStatisticsEventUrls extends FlixnDatabase {

    public function __construct()
    {
        $this->_tableName = 'event_urls';
        $this->_columns = array('id'            => ExDatabase::PARAM_INT,
                                'url'           => ExDatabase::PARAM_STR);
        $this->_columnData = array();
        
        parent::__construct(parent::DB_STATISTICS);
    }

    public function loadByUrl($url)
    {
        return $this->loadBy('url', $url);
    }
}