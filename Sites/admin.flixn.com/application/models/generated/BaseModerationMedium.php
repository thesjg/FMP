<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class BaseModerationMedium extends Doctrine_Record
{
  public function setTableDefinition()
  {
    $this->setTableName('moderation_media');
    $this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'sequence' => 'moderation_media_id'));
    $this->hasColumn('media_id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'notnull' => true));
    $this->hasColumn('state_id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'notnull' => true));
  }

}