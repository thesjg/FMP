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

class FlixnDatabaseModerationMedia extends FlixnDatabase {

    public function __construct() {
        $this->_tableName = 'moderation_media';
        $this->_columns = array('id'                    => ExDatabase::PARAM_INT,
                                'media_id'              => ExDatabase::PARAM_INT,
                                'state_id'              => ExDatabase::PARAM_INT);
        $this->_columnData = array();

        parent::__construct();
    }
}
