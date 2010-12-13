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

class FlixnDatabaseTranscodeVideoFramerates extends FlixnDatabase {

    public function __construct() {
        $this->_tableName = 'transcode_video_framerates';
        $this->_columns = array('id'        => ExDatabase::PARAM_INT,
                                'framerate' => ExDatabase::PARAM_STR);
        $this->_columnData = array();

        parent::__construct();
    }
}
