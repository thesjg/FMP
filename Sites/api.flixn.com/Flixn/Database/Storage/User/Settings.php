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

class FlixnDatabaseStorageUserSettings extends FlixnDatabase {

    public function __construct() {
        $this->_tableName = 'storage_user_settings';
        $this->_columns = array('id'                    => ExDatabase::PARAM_INT,
                                'user_id'               => ExDatabase::PARAM_INT,
                                'storage_class_id'      => ExDatabase::PARAM_INT);
        $this->_columnData = array();

        parent::__construct();
    }
}
