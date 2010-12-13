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

class FlixnDatabaseTranscodeJobs extends FlixnDatabase {

    public function __construct() {
        $this->_tableName = 'transcode_jobs';
        $this->_columns = array('id'        => ExDatabase::PARAM_INT,
                                'media_id'  => ExDatabase::PARAM_INT,
                                'video_id'  => ExDatabase::PARAM_INT,
                                'processing' => ExDatabase::PARAM_BOOL);
        $this->_columnData = array();

        parent::__construct();
    }
}