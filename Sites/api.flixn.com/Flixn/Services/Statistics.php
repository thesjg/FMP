<?php
/**
 * Flixn
 *
 * @category    Flixn
 * @package     Services
 * 
 * @author      Samuel J. Greear
 * @copyright   Copyright (c) 2008 Flixn, Inc.
 * @version     $Id $
 */

class FlixnServicesStatistics extends FlixnServices {
   
    /**
     *
     *
     * @param string $sessionId
     * @param string $instanceId
     * @param string $url
     * @return int loadid
     */
    public function addLoad($sessionId, $instanceId, $url)
    {
        $this->validateSession($sessionId);
        $this->validateComponentInstance($instanceId);
        
        $fdseu = new FlixnDatabaseStatisticsEventUrls();
        if (!$fdseu->loadByUrl($url)) {
            $fdseu->url = $url;
            $fdseu->save();
        }
        
        $fdsci = new FlixnDatabaseStatisticsComponentInstances();
        if (!$fdsci->loadByComponentId($instanceId)) {
            $fdci = new FlixnDatabaseComponentInstances();
            $fdci->loadByComponentId($instanceId);
            
            $fdu = new FlixnDatabaseUsers();
            $fdu->load($fdci->user_id);
            
            $fdsu = new FlixnDatabaseStatisticsUsers();
            if (!$fdsu->loadByUUID($fdu->uuid)) {
                $fdsu->uuid = $fdu->uuid;
                $fdsu->username = $fdu->username;
                $fdsu->save();
            }
            
            $fdsci->user_id = $fdsu->id;
            $fdsci->type_id = $fdci->type_id;
            $fdsci->component_id = $instanceId;
            $fdsci->save();
        }
        
        $fdsel = new FlixnDatabaseStatisticsEventLoad();
        $fdsel->component_instance_id = $fdsci->id;
        $fdsel->url_id = $fdseu->id;
        $fdsel->save();
        
        return $fdsel->id;
    }
    
    /**
     *
     *
     * @param string $sessionId
     * @param int $loadId
     * @return int recordid
     */
    public function addRecord($sessionId, $loadId)
    {
        $this->validateSession($sessionId);

        $fdser = new FlixnDatabaseStatisticsEventRecord();
        $fdser->load_id = $loadId;
        $fdser->save();
        
        return $fdser->id;
    }
    
    /**
     *
     *
     * @param string $sessionId
     * @param int $recordId
     * @return boolean success
     */
    public function addRecordReview($sessionId, $recordId)
    {
        $this->validateSession($sessionId);
        
        $fdserr = new FlixnDatabaseStatisticsEventRecordReview();
        $fdserr->record_id = $recordId;
        $fdserr->save();
        
        return true;
    }
    
    /**
     *
     *
     * @param string $sessionId
     * @param int $recordId
     * @return boolean success
     */
    public function addRecordComplete($sessionId, $recordId)
    {
        $this->validateSession($sessionId);
        
        $fdserc = new FlixnDatabaseStatisticsEventRecordComplete();
        $fdserc->record_id = $recordId;
        $fdserc->save();

        return true;
    }
    
    /**
     *
     * XXX: Validate timestamps
     * 
     * @param string $sessionId
     * @param int $loadId
     * @param int $fileSize
     * @param string $timeStart
     * @param string $timeComplete
     * @return boolean success
     */
    public function addUpload($sessionId, $loadId, $fileSize, $timeStart, $timeComplete)
    {
        $this->validateSession($sessionId);
        
        $fdseu = new FlixnDatabaseStatisticsEventUpload();
        $fdseu->load_id = $loadId;
        $fdseu->file_size = $fileSize;
        $fdseu->timestamp_start = $timeStart;
        $fdseu->timestamp_end = $timeComplete;
        $fdseu->save();
        
        return true;
    }
    
    public function addLoadMedia()
    {
        
    }
    
    public function addPlay()
    {
        
    }
    
    public function addPlayPause()
    {
        
    }
    
    public function addPlayStop()
    {
        
    }
    
    public function addPlaySeek()
    {
        
    }
    
    public function addPlayComplete()
    {
        
    }
        
    public function addMenu()
    {
        
    }
    
    public function addMenuAction()
    {
        
    }
}