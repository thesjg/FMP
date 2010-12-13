<?php
/**
 * Exhibition
 *
 * @category    Ex
 * @package     Database
 * @copyright   Copyright (c) 2007-2008 Evilcode Corporation
 * @version     $Id $
 */

class ExDatabaseIntrospectPgSQL {

    private $_handle;

    public function __construct($connection_handle)
    {
        $this->_handle = $connection_handle;
    }

    public function getTables()
    {
        $query = "SELECT
                      table_name,
                      table_type
                  FROM
                      information_schema.tables
                  WHERE
                      table_schema = current_schema()
                  ORDER BY table_name ASC";

        return $this->_handle->Query($query);
    }

    public function getTableComment($table)
    {
        $query = "SELECT
                      obj_description(
                          (SELECT
                              c.oid
                          FROM
                              pg_catalog.pg_class c
                          WHERE
                              c.relname = '$table'),
                              'pg_class')
                  AS
                      comment";

        return $this->_handle->Query($query);
    }

    public function getTableIndexes()
    {
    }

    public function getTableConstraints($table)
    {
        $query = "SELECT
                      tc.constraint_name,
                      tc.constraint_type,
                      ccu.column_name
                  FROM
                      information_schema.table_constraints tc,
                      information_schema.constraint_column_usage ccu
                  WHERE
                      tc.table_name = '$table'
                  AND
                      tc.constraint_name = ccu.constraint_name
                  AND
                      tc.table_name = ccu.table_name
                  AND
                      (
                          constraint_type = 'PRIMARY KEY'
                      OR
                          constraint_type = 'UNIQUE'
                      )";

        return $this->_handle->Query($query);
    }

    public function getTableReferentialConstraints()
    {
        $query = "SELECT
                      c.conname AS constraint_name,
                  CASE c.confmatchtype
                      WHEN 'f'::"char" THEN 'FULL'::text
                      WHEN 'p'::"char" THEN 'PARTIAL'::text
                      WHEN 'u'::"char" THEN 'NONE'::text
                      ELSE NULL::text
                  END::information_schema.character_data AS match_option,
                  CASE c.confupdtype
                      WHEN 'c'::"char" THEN 'CASCADE'::text
                      WHEN 'n'::"char" THEN 'SET NULL'::text
                      WHEN 'd'::"char" THEN 'SET DEFAULT'::text
                      WHEN 'r'::"char" THEN 'RESTRICT'::text
                      WHEN 'a'::"char" THEN 'NO ACTION'::text
                      ELSE NULL::text
                  END::information_schema.character_data AS update_rule,
                  CASE c.confdeltype
                      WHEN 'c'::"char" THEN 'CASCADE'::text
                      WHEN 'n'::"char" THEN 'SET NULL'::text
                      WHEN 'd'::"char" THEN 'SET DEFAULT'::text
                      WHEN 'r'::"char" THEN 'RESTRICT'::text
                      WHEN 'a'::"char" THEN 'NO ACTION'::text
                     ELSE NULL::text
                  END::information_schema.character_data AS delete_rule,
                      clt.relname AS target_table,
                      array_to_string(c.confkey, ',') AS target_columns
                  FROM
                      pg_catalog.pg_class cl,
                      pg_catalog.pg_constraint c
                  LEFT JOIN
                      pg_catalog.pg_class clt
                  ON
                      (clt.oid = c.confrelid)
                  WHERE
                      c.contype = 'f'
                  AND
                      cl.oid = c.conrelid
                  AND
                      cl.relname = '$table'";

        return $this->_handle->Query($query);
    }

    public function setTableComment($table, $comment)
    {
        $query = "COMMENT ON TABLE
                      $table
                  IS
                      '$comment'";

        return $this->_handle->Query($query);
    }

    public function getColumnsForTable($table)
    {
        $query = "SELECT
                      column_name
                      ordinal_position
                      column_default
                      is_nullable
                      data_type
                      character_maximum_length
                  FROM
                      information_schema.tables
                  WHERE
                      table_schema = current_schema()
                  AND
                      table_name = '$table'";

        return $this->_handle->Query($query);
    }

    public function getColumnComment($table, $column)
    {
        $query = "SELECT
                      col_description(
                          (SELECT
                              c.oid
                          FROM
                              pg_catalog.pg_class c
                          WHERE
                              c.relname='$table'),
                          (SELECT
                              a.attnum
                          FROM
                              pg_catalog.pg_class c,
                              pg_catalog.pg_attribute a
                          WHERE
                              a.attrelid=c.oid
                          AND
                              c.relname='$table'
                          AND
                              a.attname='$column'))"

        return $this->_handle->Query($query);
    }

    public function setColumnComment($table, $column, $comment)
    {
        $query = "COMMENT ON COLUMN
                      $table.$column
                  IS
                      '$comment'";

        return $this->_handle->Query($query);
    }
}