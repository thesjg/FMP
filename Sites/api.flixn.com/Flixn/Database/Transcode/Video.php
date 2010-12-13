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

class FlixnDatabaseTranscodeVideo extends FlixnDatabase {

    public function __construct() {
        $this->_tableName = 'transcode_video';
        $this->_columns = array('id'        => ExDatabase::PARAM_INT,
                                'user_id'   => ExDatabase::PARAM_INT,
                                'name'      => ExDatabase::PARAM_STR,
                                'media_video_format' => ExDatabase::PARAM_INT,
                                'audio_id'  => ExDatabase::PARAM_INT,
                                'width'     => ExDatabase::PARAM_INT,
                                'height'    => ExDatabase::PARAM_INT,
                                'framerate' => ExDatabase::PARAM_INT,
                                'bitrate'   => ExDatabase::PARAM_INT);
        $this->_columnData = array();

        parent::__construct();
    }
}
