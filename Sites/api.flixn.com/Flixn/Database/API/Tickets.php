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

class FlixnDatabaseAPITickets extends FlixnDatabase {
    
    public function __construct()
    {
        $this->_tableName = 'api_tickets';
        $this->_columns = array('id'                    => ExDatabase::PARAM_INT,
                                'component_instance_id' => ExDatabase::PARAM_INT,
                                'session_id'            => ExDatabase::PARAM_INT,
                                'media_id'              => ExDatabase::PARAM_INT,
                                'ticket_id'             => ExDatabase::PARAM_STR,
                                'created'               => ExDatabase::PARAM_STR);
        $this->_columnData = array();
        
        parent::__construct();
    }
    
    public function loadBySessionId($sessionId)
    {
        return $this->loadBy('session_id', $sessionId);
    }
    
    public function loadByTicketId($ticketId)
    {
        return $this->loadBy('ticket_id', $ticketId);
    }
}