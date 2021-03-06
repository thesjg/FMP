<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class BaseComponentDomainEntry extends Doctrine_Record
{
  public function setTableDefinition()
  {
    $this->setTableName('component_domain_entries');
    $this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'sequence' => 'component_domain_entries_id'));
    $this->hasColumn('list_id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'notnull' => true));
    $this->hasColumn('domain', 'string', 255, array('type' => 'string', 'length' => '255', 'notnull' => true));
  }

}