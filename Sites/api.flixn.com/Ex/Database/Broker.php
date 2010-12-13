<?php
/**
 * Exhibition
 *
 * @category    Ex
 * @package     Database
 * @copyright   Copyright (c) 2007-2008 Evilcode Corporation
 * @version     $Id $
 */

class ExDatabaseBroker {

    /**
     * Singleton instance
     * @var self
     */
    private static $_instance = null;

    private $_connections;

    private function __construct()
    {
        $this->_connections = array();
    }

    /**
     * Singleton instance
     *
     * @return ExDatabaseBroker
     */
    public static function getInstance()
    {
        if (self::$_instance === null)
            self::$_instance = new self();

        return self::$_instance;
    }

    public function getHandle($arr)
    {
        return $this->_getHandle($arr['username'], $arr['password'],
                                 $arr['database'], $arr['hostname'],
                                 $arr['socket'], $arr['driver']);
    }

    private function _getHandle($username, $password, $database, $hostname,
                                $socket = null, $driver = 'pgsql')
    {
//        try {
            $dsn = ExDatabase::CreateDSN($database, $hostname, $driver);

            $db_unique_dsn = $dsn . ';user=' . $username;
            if (isset($this->_connections[$db_unique_dsn])) {
                foreach ($this->_connections[$db_unique_dsn] as $connection)
//                    if (!$connection->InTransaction())
                        return $connection;
            }

            if (!isset($this->_connections[$db_unique_dsn]))
                $this->_connections[$db_unique_dsn] = array();

            $handle = new ExDatabase($dsn, $username, $password);
            $this->_connections[$db_unique_dsn][] = $handle;
            return $handle;
//        } catch (Exception $e) {
//            throw new ExDatabaseException();
//        }
    }
}