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
 *
 */
class FlixnServices {
    
    public function __construct() {
    }
    
    /**
     * Validate session
     *
     * @param string $sessionId
     */
    protected function validateSession($sessionId) {

        if (new FlixnSession($sessionId) === false)
            throw new FlixnServicesException(FlixnServicesErrors::INVALID_SESSION_IDENTIFIER);
        
        return true;
    }
    
    /**
     * Validate component instance identifier
     *
     * @param string $instanceId
     */
    protected function validateComponentInstance($instanceId) {

        $fdci = new FlixnDatabaseComponentInstances();
        if (!$fdci->loadByComponentId($instanceId))
            throw new FlixnServicesException(FlixnServicesErrors::INVALID_COMPONENT_IDENTIFIER);
    
        return true;
    }
    
    /**
     * Create a new ticket
     *
     * @param string $sessionId
     * @param string $instanceId
     * @return string Ticket identifier
     */
    protected function createAPITicket($sessionId, $instanceId) {

        /**
         * Generate a unique ticket id
         *
         * @todo Use a proper ident
         */
        $fi = new FlixnIdentification();
        $ticket_id = $fi->UUIDSessionGenerate(1);
        
        $fdci = new FlixnDatabaseComponentInstances();
        $fdci->loadByComponentId($instanceId);

        $fds = new FlixnDatabaseSession();
        $fds->loadBySessionId($sessionId);
        
        /**
         * Drop ticket into the database
         *
         * @todo Verify database insertion success
         */
        $fdat = new FlixnDatabaseAPITickets();
        $fdat->component_instance_id = $fdci->id;
        $fdat->session_id = $fds->id;
        $fdat->ticket_id = $ticket_id;
        $fdat->save();
        
        return $ticket_id;
    }
    
    /**
     * Load the ticket in question
     *
     * @param string $ticketId
     * @return FlixnDatabaseAPITickets
     */
    protected function getAPITicket($ticketId) {

        $fdat = new FlixnDatabaseAPITickets();
        if (!$fdat->loadByTicketId($ticketId))
            throw new FlixnServicesException(FlixnServicesErrors::INVALID_TICKET_IDENTIFIER);
        
        /**
         * @todo Verify that ticket is valid / still valid
         */

        return $fdat;
    }
}