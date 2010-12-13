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

class FlixnDatabaseTranscodeComponentAudio extends FlixnDatabase {
    
    public function __construct() {
        $this->_tableName = 'transcode_component_audio';
        $this->_columns = array('id'        => ExDatabase::PARAM_INT,
                                'component_id' => ExDatabase::PARAM_INT,
                                'audio_id'  => ExDatabase::PARAM_INT);
        $this->_columnData = array();
        
        parent::__construct();
    }
}