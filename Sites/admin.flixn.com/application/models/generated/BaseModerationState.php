<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class BaseModerationState extends Doctrine_Record
{
  public function setTableDefinition()
  {
    $this->setTableName('moderation_states');
    $this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'sequence' => 'moderation_states_id'));
    $this->hasColumn('name', 'string', 63, array('type' => 'string', 'length' => '63', 'notnull' => true));
  }

}