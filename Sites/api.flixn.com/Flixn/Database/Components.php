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
  
class FlixnDatabaseComponents extends FlixnDatabase {

    public function __construct()
    {
        $this->tableName = 'components';
        $this->_columns = array('id'                    => ExDatabase::PARAM_INT,
                                'user_id'               => ExDatabase::PARAM_INT,
                                'component_type_id'     => ExDatabase::PARAM_INT,
                                'component_profile_id'  => ExDatabase::PARAM_INT,
                                'component_id'          => ExDatabase::PARAM_STR,
                                'component_key'         => ExDatabase::PARAM_STR,
                                'name'                  => ExDatabase::PARAM_STR,
                                'restrict_domains'      => ExDatabase::PARAM_STR);
        $this->_columnData = array();
        
        parent::__construct();
    }
}