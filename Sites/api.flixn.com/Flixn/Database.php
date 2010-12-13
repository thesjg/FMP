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

/*
 * FlixnDatabase
 *
 * @todo Move _connInfo to global/otherwise configuration settings
 *
 * XXX: This is a fucking mess
 */
class FlixnDatabase {

    const DB_MAIN       = 'main';
    const DB_STATISTICS = 'statistics';

    protected $_tableName;
    protected $_columns;
    protected $_columnData;

    private $_connInfo;

    private $_broker;
    private $_handle;

    public $printQueries = false;

    public function __construct($db=FlixnDatabase::DB_MAIN) {
        if ($db == FlixnDatabase::DB_MAIN) {
            $this->_connInfo = array('username' => '',
                                     'password' => '',
                                     'database' => '',
                                     'hostname' => '',
                                     'socket' => NULL,
                                     'driver' => 'pgsql');
        } else if ($db == FlixnDatabase::DB_STATISTICS) {
            $this->_connInfo = array('username' => '',
                                     'password' => '',
                                     'database' => '',
                                     'hostname' => '',
                                     'socket' => NULL,
                                     'driver' => 'pgsql');
        }

        $this->_broker = ExDatabaseBroker::getInstance();
        $this->_handle = $this->_broker->getHandle($this->_connInfo);
    }

    public function __get($param) {
        return $this->_columnData[$param];
    }

    public function __set($param, $value) {
        $this->_columnData[$param] = $value;
    }

    public function __isset($param) {
        return isset($this->_columnData[$param]);
    }

    public function __unset($param) {
        unset($this->_columnData[$param]);
    }

    public function load($id) {
        $query = 'SELECT ' . $this->_buildColumnList() . ' FROM '
               . $this->_tableName . ' WHERE id=' . $id;

        if ($this->printQueries)
            print $query;

        $result = $this->_handle->getRow($query);
        $i = 0;
        foreach ($this->_columns as $key => $value) {
            $this->_columnData[$key] = $result[$i];
            $i++;
        }
        return true;
    }

    public function loadAll($where=NULL) {
        $query = 'SELECT ' . $this->_buildColumnList() . ' FROM ' . $this->_tableName;

        if ($where != NULL)
            $query .= ' WHERE ' . $where;

        if ($this->printQueries)
            print $query;

        return $this->query($query);
    }

    public function loadOne($where=NULL) {
        $query = 'SELECT ' . $this->_buildColumnList() . ' FROM '
               . $this->_tableName . ' $where ORDER BY id LIMIT 1';

        if ($where !== NULL)
            $query = str_replace('$where', 'WHERE ' . $where, $query);

        if ($this->printQueries)
            print $query;

        if (!$result = $this->_handle->getRow($query))
            return false;

        foreach ($result as $key => $value)
            if (!is_numeric($key))
                $this->_columnData[$key] = $value;

        return true;
    }

    public function loadBy($colname, $colvalue) {
        $columns = array();
        foreach ($this->_columns as $key => $value)
            if ($colname != $key)
                $columns[] = $key;

        $cols = implode(', ', $columns);
        $query = 'SELECT ' . $cols . ' FROM ' . $this->_tableName
               . ' WHERE ' . $colname . "='" . $colvalue . "'";

        if ($this->printQueries)
            print $query;

        if (!$result = $this->_handle->getRow($query))
            return false;

        foreach ($result as $key => $value)
            if (!is_numeric($key))
                $this->_columnData[$key] = $value;

        return true;
    }

    public function loadByMany($many) {
        $columns = array();
        foreach ($this->_columns as $key => $value)
            if (!array_key_exists($key, $many))
                $columns[] = $key;

        $cols = implode(', ', $columns);
        $query = 'SELECT ' . $cols . ' FROM ' . $this->_tableName
               . ' WHERE ';

        $i = 1;
        foreach ($many as $key => $value) {
            $query .= $key . "='" . $value . "'";

            if ($i < count($many))
                $query .= ' AND ';

            $i++;
        }

        if ($this->printQueries)
            print $query;

        if (!$result = $this->_handle->getRow($query))
            return false;

        foreach ($result as $key => $value)
            if (!is_numeric($key))
                $this->_columnData[$key] = $value;

        return true;
    }

    public function save() {
        if (isset($this->_columnData['id'])) {
            $query = 'UPDATE ' . $this->_tableName . ' SET ';
            foreach ($this->_columnData as $key => $value) {
                if ($key == 'id')
                    continue;

                $query .= $key . "='" . $value . "', ";
            }
            $query = substr($query, 0, strlen($query)-2);
            $query .= ' WHERE id=' . $this->_columnData['id'];

            if ($this->printQueries)
                print $query;

            $this->_handle->query($query);
        } else {
            $query = 'INSERT INTO ' . $this->_tableName . ' (';
            foreach ($this->_columnData as $key => $value) {
                $query .= $key . ", ";
            }
            $query = substr($query, 0, strlen($query)-2);
            $query .= ') VALUES (';
            foreach ($this->_columnData as $value) {
                if ($value === NULL)
                    $query .= 'NULL, ';
                else
                    $query .= "'" . $value . "', ";
            }
            $query = substr($query, 0, strlen($query)-2);
            $query .= ')';

            if ($this->printQueries)
                print $query;

            $this->_handle->query($query);

            $this->_columnData['id'] = $this->_handle->insertId($this->_tableName . '_id_seq');
        }
    }

    public function delete($id=NULL) {
        if ($id !== NULL)
            $query = 'DELETE FROM ' . $this->_tableName . ' WHERE id=' . $id;
        else if (isset($this->_columnData['id']))
            $query = 'DELETE FROM ' . $this->_tableName . ' WHERE id=' . $this->_columnData['id'];
        else
            return false;

        return $this->_handle->query($query);
    }

    private function _buildColumnList() {
        $columns = array();
        foreach ($this->_columns as $key => $value)
            $columns[] = $key;

        return implode(', ', $columns);
    }

    public function query($query) {
        return $this->_handle->query($query);
    }
}