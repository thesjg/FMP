<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class BaseComponentInstance extends Doctrine_Record
{
  public function setTableDefinition()
  {
    $this->setTableName('component_instances');
    $this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'sequence' => 'component_instances_id'));
    $this->hasColumn('user_id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'notnull' => true));
    $this->hasColumn('type_id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'notnull' => true));
    $this->hasColumn('component_id', 'string', null, array('type' => 'string', 'notnull' => true));
    $this->hasColumn('component_key', 'string', null, array('type' => 'string', 'notnull' => true));
    $this->hasColumn('name', 'string', 63, array('type' => 'string', 'length' => '63', 'notnull' => true));
    $this->hasColumn('restrict_domains', 'boolean', 1, array('type' => 'boolean', 'length' => 1, 'notnull' => true, 'default' => 'false'));
    $this->hasColumn('created', 'timestamp', null, array('type' => 'timestamp', 'notnull' => true, 'default' => 'now()'));
    $this->hasColumn('active', 'boolean', 1, array('type' => 'boolean', 'length' => 1, 'notnull' => true, 'default' => 'true'));
    $this->hasColumn('state_id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'notnull' => true, 'default' => '1'));
  }

}