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

class FlixnDatabaseStorageUserSettingsS3 extends FlixnDatabase {

    public function __construct() {
        $this->_tableName = 'storage_user_settings_s3';
        $this->_columns = array('id'                    => ExDatabase::PARAM_INT,
                                'user_id'               => ExDatabase::PARAM_INT,
                                'bucket'                => ExDatabase::PARAM_STR,
                                'token'                 => ExDatabase::PARAM_STR);
        $this->_columnData = array();

        parent::__construct();
    }
}
