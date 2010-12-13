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

class FlixnDatabaseComponentPlayerSettings extends FlixnDatabase {

    public function __construct() {
        $this->_tableName = 'component_player_settings';
        $this->_columns = array('id'                    => ExDatabase::PARAM_INT,
                                'instance_id'           => ExDatabase::PARAM_INT,
                                'embed'                 => ExDatabase::PARAM_BOOL,
                                'share'                 => ExDatabase::PARAM_BOOL,
                                'email'                 => ExDatabase::PARAM_BOOL,
                                'sms'                   => ExDatabase::PARAM_BOOL,
                                'info'                  => ExDatabase::PARAM_BOOL,
                                'fullscreen'            => ExDatabase::PARAM_BOOL,
                                'popout'                => ExDatabase::PARAM_BOOL,
                                'lighting'              => ExDatabase::PARAM_BOOL,
                                'autoplay'              => ExDatabase::PARAM_BOOL);
        $this->_columnData = array();

        parent::__construct();
    }

    public function loadByInstanceId($instanceId) {
        return $this->loadBy('instance_id', $instanceId);
    }
}
