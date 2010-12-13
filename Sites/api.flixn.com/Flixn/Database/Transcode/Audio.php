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

class FlixnDatabaseTranscodeAudio extends FlixnDatabase {
    
    public function __construct() {
        $this->_tableName = 'transcode_audio';
        $this->_columns = array('id'        => ExDatabase::PARAM_INT,
                                'media_audio_format' => ExDatabase::PARAM_INT,
                                'bitrate'   => ExDatabase::PARAM_INT);
        $this->_columnData = array();
        
        parent::__construct();
    }
}