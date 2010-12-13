<?php
/**
 * Exhibition
 *
 * @category    Ex
 * @package     Database
 * @subpackage  Statement
 * @copyright   Copyright (c) 2005-2007 Samuel J. Greear
 * @copyright   Copyright (c) 2007-2008 Evilcode Corporation
 * @version     $Id $
 */

class ExDatabaseStatement extends PDOStatement implements ExDatabaseStatementInterface {

    public function bindColumn($column, &$param, $type=NULL, $maxlen=NULL, $driverdata=NULL)
    {
        return parent::bindColumn($column, $param, $type);
    }

    public function bindParam($parameter, &$variable, $data_type=NULL, $length=NULL, $driver_options=NULL)
    {
        return parent::bindParam($parameter, $variable, $data_type, $length, $driver_options);
    }

    public function bindValue($parameter, $value, $data_type=NULL)
    {
        return parent::bindValue($parameter, $value, $data_type);
    }

    public function closeCursor()
    {
        return parent::closeCursor();
    }

    public function columnCount()
    {
        return parent::columnCount();
    }

// PDOStatement::columnCount
// PDOStatement::errorCode
// PDOStatement::errorInfo

    public function execute($parameters=NULL)
    {
        return parent::execute($parameters);
    }

    public function fetch($fetch_style=NULL, $cursor_orientation=NULL, $cursor_offset=NULL)
    {
        return parent::fetch($fetch_style, $cursor_orientation, $cursor_offset);
    }

    public function fetchAll($fetch_style=NULL, $column_index=NULL, $ctor_args=NULL)
    {
        return parent::fetchAll($fetch_style, $column_index, $ctor_args);
    }

    public function fetchColumn($column_number=NULL)
    {
        return parent::fetchColumn($column_number);
    }

// PDOStatement::fetchObject
// PDOStatement::getAttribute
// PDOStatement::getColumnMeta
// PDOStatement::nextRowset

    public function rowCount()
    {
        return parent::rowCount();
    }

// PDOStatement::setAttribute
// PDOStatement::setFetchMode

}