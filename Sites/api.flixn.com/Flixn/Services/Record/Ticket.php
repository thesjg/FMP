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
 * @property string $ticketId
 * @property string $endpoint
 * @property string $instance
 * @property string $filename
 */
class FlixnServicesRecordTicket extends FlixnServicesReturn {
    public $ticketId;
    public $endpoint;
    public $instance;
    public $filename;
}