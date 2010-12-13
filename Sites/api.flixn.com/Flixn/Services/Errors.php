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

class FlixnServicesErrors extends ExServicesErrors {

    const INVALID_SESSION_IDENTIFIER   = '1100';
    const INVALID_COMPONENT_IDENTIFIER = '1101';
    const INVALID_TICKET_IDENTIFIER    = '1102';
    
    const UNSUPPORTED_UPLOAD_METHOD    = '1200';

    public function __construct() {
        parent::__construct();
        
        $errors = array(
            self::INVALID_SESSION_IDENTIFIER   => "xxx",
            self::INVALID_COMPONENT_IDENTIFIER => "Unable to locate settings based on component identifier.",
            self::INVALID_TICKET_IDENTIFIER    => "xxx",
            self::UNSUPPORTED_UPLOAD_METHOD    => "The requested upload method is unsupported or is currently unavailable."
        );

        foreach ($errors as $code => $error)
            $this->addError($code, $error);
    }
}