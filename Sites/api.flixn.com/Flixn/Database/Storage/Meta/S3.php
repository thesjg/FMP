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

class FlixnDatabaseStorageMetaS3 extends FlixnDatabase {

    public function __construct() {
        $this->_tableName = 'storage_meta_s3';
        $this->_columns = array('id'                    => ExDatabase::PARAM_INT,
                                'media_x_uuid'          => ExDatabase::PARAM_STR,
                                'uploading'             => ExDatabase::PARAM_BOOL,
                                'available'             => ExDatabase::PARAM_BOOL);
        $this->_columnData = array();

        parent::__construct();
    }
}
