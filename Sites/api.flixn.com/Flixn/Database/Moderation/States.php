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

class FlixnDatabaseModerationStates extends FlixnDatabase {

    public function __construct() {
        $this->_tableName = 'moderation_states';
        $this->_columns = array('id'                    => ExDatabase::PARAM_INT,
                                'name'                  => ExDatabase::PARAM_STR);
        $this->_columnData = array();

        parent::__construct();
    }
}
