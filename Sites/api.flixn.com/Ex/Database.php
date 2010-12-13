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

/**
 * TODO: Beef up exception handling
 * TODO: Set transaction=false on exceptions
 */
class ExDatabase extends PDO implements ExDatabaseInterface {

    const FETCH_ASSOC = PDO::FETCH_ASSOC;
    const FETCH_BOTH = PDO::FETCH_BOTH;
    const FETCH_BOUND = PDO::FETCH_BOUND;
    const FETCH_CLASS = PDO::FETCH_CLASS;
    const FETCH_INFO = PDO::FETCH_INTO;
    const FETCH_LAZY = PDO::FETCH_LAZY;
    const FETCH_NUM = PDO::FETCH_NUM;
    const FETCH_OBJ = PDO::FETCH_OBJ;

    const PARAM_STR = PDO::PARAM_STR;
    const PARAM_INT = PDO::PARAM_INT;
    const PARAM_BOOL = PDO::PARAM_BOOL;
    const PARAM_NULL = PDO::PARAM_NULL;

    private $Handle;
    private $DBType;

    private $Transaction;

    public function __construct($dsn, $username=NULL, $password=NULL)
    {
        $this->Handle = NULL;
        $this->Transaction = false;

        $dsn .= ';user=' . $username;
        parent::__construct($dsn, $username, $password);

        $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function Begin()
    {
        $this->Transaction = true;
        parent::beginTransaction();
    }

    public function Commit()
    {
        $this->Transaction = false;
        parent::commit();
    }

    public function Rollback()
    {
        $this->Transaction = false;
        parent::rollback();
    }

    public function InsertID($name=NULL)
    {
        return parent::lastInsertId($name);
    }

    public function Prepare($statement, $driver_options=NULL)
    {
//        if ($this->Handle !== NULL)
//            $this->Handle->closeCursor();

        $this->Handle = parent::prepare($statement,
                                  array(PDO::ATTR_STATEMENT_CLASS =>
                                        array('ExDatabaseStatement')));

        if (!$this->Handle instanceof PDOStatement) {
            $error_info = parent::errorInfo();
            throw new ExDatabaseException('Could not prepare query: ' . $error_info[2]);
        }

        return $this->Handle;
    }

    public function Execute($statement)
    {
        return parent::exec($statement);
    }

    public function Query($statement)
    {
        $prepared_statement = $this->Prepare($statement);
        $prepared_statement->Execute();
        return $prepared_statement;
    }

    public function GetRow($statement)
    {
        $result = $this->Query($statement);
        if ($result === false)
            return false;
        
        return $result->Fetch();
    }

    public static function CreateDSN($database, $hostname=NULL, $driver='pgsql')
    {
        $dsn = $driver . ':';

        if ($hostname == NULL)
            $hostname = 'localhost';

        $dsn .= 'dbname=' . $database . ';host=' . $hostname;
        return $dsn;
    }

    public static function Connect($arr)
    {
        $dsn = ExDatabase::CreateDSN($arr['database'], $arr['hostname'], $arr['driver']);
        return new ExDatabase($app, $dsn, $arr['username'], $arr['password']);
    }
}