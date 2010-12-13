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

class FlixnDatabaseModerationComponents extends FlixnDatabase {

    public function __construct() {
        $this->_tableName = 'moderation_components';
        $this->_columns = array('id'                    => ExDatabase::PARAM_INT,
                                'instance_id'           => ExDatabase::PARAM_INT,
                                'component_instance_id' => ExDatabase::PARAM_INT);
        $this->_columnData = array();

        parent::__construct();
    }
}
