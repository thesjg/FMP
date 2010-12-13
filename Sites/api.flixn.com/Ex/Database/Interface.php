<?php
/**
 * Exhibition
 *
 * @category    Ex
 * @package     Database
 * @copyright   Copyright (c) 2005-2007 Samuel J. Greear
 * @copyright   Copyright (c) 2007-2008 Evilcode Corporation
 * @version     $Id $
 */

interface ExDatabaseInterface {
    public function Begin();
    public function Commit();
    public function Rollback();
    public function InsertID();
    public function Prepare($statement, $driver_options=NULL);
    public function Execute($statement);
    public function Query($statement);
    public function GetRow($statement);
}