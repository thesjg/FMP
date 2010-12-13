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

class FlixnDatabaseMediaMetadataCreation extends FlixnDatabase {

    public function __construct() {
        $this->_tableName = 'media_metadata_creation';
        $this->_columns = array('id'        => ExDatabase::PARAM_INT,
                                'media_id'  => ExDatabase::PARAM_INT,
                                'component_id' => ExDatabase::PARAM_INT,
                                'url'       => ExDatabase::PARAM_STR,
                                'timestamp' => ExDatabase::PARAM_STR);
        $this->_columnData = array();

        parent::__construct();
    }
}