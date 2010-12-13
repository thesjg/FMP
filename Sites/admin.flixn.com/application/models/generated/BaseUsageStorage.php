<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class BaseUsageStorage extends Doctrine_Record
{
  public function setTableDefinition()
  {
    $this->setTableName('usage_storage');
    $this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'sequence' => 'usage_storage_id'));
    $this->hasColumn('user_id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'notnull' => true));
    $this->hasColumn('storage', 'integer', 8, array('type' => 'integer', 'length' => 8, 'notnull' => true));
    $this->hasColumn('time', 'timestamp', null, array('type' => 'timestamp', 'notnull' => true, 'default' => 'now()'));
  }

}