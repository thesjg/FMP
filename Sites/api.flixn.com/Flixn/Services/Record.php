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

class FlixnServicesRecord extends FlixnServices {

    public function __construct() {
    }
    
    /**
     * Initiate a recorder ticket
     *
     * @todo Programmatic endpoint
     * 
     * @param string $sessionId
     * @param string $instanceId
     * @return FlixnServicesRecordTicket recordticket
     */
    public function getTicket($sessionId, $instanceId) {

        $this->validateSession($sessionId);
        $this->validateComponentInstance($instanceId);

        $ticket_id = $this->createAPITicket($sessionId, $instanceId);
        
        /**
         * Create and return record ticket
         */
        $ret = new FlixnServicesRecordTicket();
        $ret->ticketId = $ticket_id;
        $ret->endpoint = 'rtmp://record.flixn.in.evilprojects.net';
        $ret->instance = '';
        $ret->filename = '';
        
        return $ret;
    }

    /**
     * Retrieve settings for and limits that have been placed on this particular
     * component instance. Limits are potentially artificial and only enforced
     * by the component implementation.
     *
     * @todo Support additional limits such as bitrate or resolution
     * @todo v2 Break this into "classes" ?
     *
     * @param string $ticketId
     * @return FlixnServicesRecordSettings recordsettings
     */
    public function getSettings($ticketId) {
        
        $ticket = $this->getAPITicket($ticketId);
        
        /**
         * Load settings for the component referenced by the ticket id
         */
        $fdcrs = new FlixnDatabaseComponentRecorderSettings();
        if (!$fdcrs->loadByInstanceId($ticket->component_instance_id))
            throw new FlixnServicesException(FlixnServicesErrors::INVALID_COMPONENT_IDENTIFIER);

        /**
         * Create and return record settings
         */
        $ret = new FlixnServicesRecordSettings();
        $ret->timeLimit = $fdcrs->time_limit;
        
        return $ret;
    }
    
    /**
     * Migrate audio/video to stable storage and generate a persistent media
     * identifier for it. 
     *
     * @internal FMS
     *
     * @param string $ticketId
     * @return string mediaid
     */
    public function publish($ticketId) {
    
        return $mediaId;
    }
}