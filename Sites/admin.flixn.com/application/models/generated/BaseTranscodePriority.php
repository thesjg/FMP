<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class BaseTranscodePriority extends Doctrine_Record
{
  public function setTableDefinition()
  {
    $this->setTableName('transcode_priority');
    $this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'sequence' => 'transcode_priority_id'));
    $this->hasColumn('component_id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'notnull' => true));
    $this->hasColumn('transcode_id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'notnull' => true));
    $this->hasColumn('priority', 'integer', 4, array('type' => 'integer', 'length' => 4, 'notnull' => true));
  }

}