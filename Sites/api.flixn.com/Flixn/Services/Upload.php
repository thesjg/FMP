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

/**
 * FlixnServicesUpload
 */
class FlixnServicesUpload extends FlixnServices {
    
    /**
     * Method is one of post or put
     *
     * @todo Programmatic endpoint
     * 
     * @param string $sessionId
     * @param string $instanceId
     * @param string $method
     * @return FlixnServicesUploadTicket uploadticket
     */
    public function getTicket($sessionId, $instanceId, $method='post') {
        
        /**
         * @todo We should support PUT requests
         */
        if ($method == 'put')
            throw new FlixnServicesException(FlixnServicesErrors::UNSUPPORTED_UPLOAD_METHOD);

        $this->validateSession($sessionId);
        $this->validateComponentInstance($instanceId);

        $ticket_id = $this->createAPITicket($sessionId, $instanceId);
    
        /**
         * Create and return upload ticket
         */
        $ticket = new FlixnServicesUploadTicket();
        $ticket->ticketId = $ticket_id;
        $ticket->endpoint = 'http://upload.flixn.evilprojects.net';
        
        return $ticket;
    }

    /**
     * Maps to something flash can use for file selection ui element
     *
     * @todo Tie to database
     *
     * @param string $ticketId
     * @return FlixnServicesUploadFilters[] uploadfilters
     */
    public function getFilters($ticketId) {
    
        $fil1 = new FlixnServicesUploadFilters();
        $fil1->desription = 'Movies/Videos';
        $fil1->extensions = 'flv,mov,avi';
    
        return array($fil1);
    }
    
    /**
     * Upload size limits (in bytes)
     *
     * @param string $ticketId
     * @return FlixnServicesUploadLimits uploadlimits
     */
    public function getLimits($ticketId) {
    
        $ticket = $this->getAPITicket($ticketId);
        
        /**
         * Load settings for the component referenced by the ticket id
         */
        $fdcus = new FlixnDatabaseComponentUploaderSettings();
        if (!$fdcus->loadByInstanceId($ticket->component_instance_id))
            throw new FlixnServicesException(FlixnServicesErrors::INVALID_COMPONENT_IDENTIFIER);
    
        /**
         * Create and return limits structure
         * No transformations necessary as sizes are stored as bytes
         * in the database.
         */
        $limits = new FlixnServicesUploadLimits();
        $limits->fileBytes = $fdcus->file_limit;
        $limits->totalBytes = $fdcus->size_limit;
        
        return $limits;
    }

    /**
     *
     * @param string $ticketId
     * @return string mediaid
     */
    public function getMediaId($ticketId) {
    
        return $media_id;
    }
}