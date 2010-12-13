<?php
/**
 * Exhibition
 *
 * @category    Ex
 * @package     Database
 * @subpackage  Statement
 * @copyright   Copyright (c) 2007-2008 Evilcode Corporation
 * @version     $Id $
 */

interface ExDatabaseStatementInterface {
    public function bindColumn($column, &$param, $type=NULL, $maxlen=NULL, $driverdata=NULL);
    public function bindParam($parameter, &$variable, $data_type=NULL, $length=NULL, $driver_options=NULL);
    public function bindValue($parameter, $value, $data_type=NULL);
    public function closeCursor();
    public function columnCount();
    public function execute($parameters=NULL);
    public function fetch($fetch_style=NULL, $cursor_orientation=NULL, $cursor_offset=NULL);
    public function fetchAll($fetch_style=NULL, $column_index=NULL, $ctor_args=NULL);
    public function fetchColumn($column_number=NULL);
    public function rowCount();
}