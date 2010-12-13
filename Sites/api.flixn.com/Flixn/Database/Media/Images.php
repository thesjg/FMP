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

class FlixnDatabaseMediaImages extends FlixnDatabase {

    public function __construct() {
        $this->_tableName = 'media_images';
        $this->_columns = array('id'        => ExDatabase::PARAM_INT,
                                'media_id'  => ExDatabase::PARAM_INT,
                                'format_id' => ExDatabase::PARAM_INT,
                                'storage_class_id' => ExDatabase::PARAM_INT,
                                'original'  => ExDatabase::PARAM_BOOL,
                                'size'      => ExDatabase::PARAM_INT,
                                'created'   => ExDatabase::PARAM_STR,
                                'media_image_id' => ExDatabase::PARAM_STR);
        $this->_columnData = array();

        parent::__construct();
    }
}
