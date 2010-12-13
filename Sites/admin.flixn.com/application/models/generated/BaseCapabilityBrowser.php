<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class BaseCapabilityBrowser extends Doctrine_Record
{
  public function setTableDefinition()
  {
    $this->setTableName('capabilities_browser');
    $this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'sequence' => 'capabilities_browser_id'));
    $this->hasColumn('session_id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'notnull' => true));
    $this->hasColumn('user_agent', 'string', 63, array('type' => 'string', 'length' => '63', 'notnull' => true));
  }

}