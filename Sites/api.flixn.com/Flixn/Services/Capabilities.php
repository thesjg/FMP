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

class FlixnServicesCapabilities extends FlixnServices {

    public function __construct() {
    }

    /**
     *
     *
     * @param string $ticketId
     * @return boolean shouldpublish
     */
    public function shouldPublish($ticketId) {
        return true;
    }

    /**
     *
     *
     * @param string $ticketId
     * @return boolean success
     */
    public function flash($ticketId) {
        
        $ticket = $this->getAPITicket($ticketId);

        return true;
    }

    /**
     *
     *
     * @param string $ticketId
     * @return boolean success
     */
    public function browser($ticketId) {
        
        $ticket = $this->getAPITicket($ticketId);
        
        return true;
    }
}