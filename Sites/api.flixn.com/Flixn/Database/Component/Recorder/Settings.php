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

class FlixnDatabaseComponentRecorderSettings extends FlixnDatabase {

    public function __construct() {
        $this->_tableName = 'component_recorder_settings';
        $this->_columns = array('id'                    => ExDatabase::PARAM_INT,
                                'instance_id'           => ExDatabase::PARAM_INT,
                                'video'                 => ExDatabase::PARAM_BOOL,
                                'high_quality'          => ExDatabase::PARAM_INT,
                                'time_limit'            => ExDatabase::PARAM_INT,
                                'style_id'              => ExDatabase::PARAM_INT);
        $this->_columnData = array();

        parent::__construct();
    }

    public function loadByInstanceId($instanceId) {
        return $this->loadBy('instance_id', $instanceId);
    }
}