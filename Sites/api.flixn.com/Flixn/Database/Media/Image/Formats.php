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

class FlixnDatabaseMediaImageFormats extends FlixnDatabase {
    
    public function __construct() {
        $this->_tableName = 'media_image_formats';
        $this->_columns = array('id'        => ExDatabase::PARAM_INT,
                                'format'    => ExDatabase::PARAM_STR,
                                'description' => ExDatabase::PARAM_STR);
        $this->_columnData = array();
        
        parent::__construct();
    }
}