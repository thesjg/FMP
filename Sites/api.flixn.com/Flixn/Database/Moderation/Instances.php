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

class FlixnDatabaseModerationInstances extends FlixnDatabase {

    public function __construct() {
        $this->_tableName = 'moderation_instances';
        $this->_columns = array('id'                    => ExDatabase::PARAM_INT,
                                'user_id'               => ExDatabase::PARAM_INT,
                                'name'                  => ExDatabase::PARAM_STR,
                                'deferred'              => ExDatabase::PARAM_BOOL);
        $this->_columnData = array();

        parent::__construct();
    }
}
