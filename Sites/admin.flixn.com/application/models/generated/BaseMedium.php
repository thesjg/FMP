<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class BaseMedium extends Doctrine_Record
{
  public function setTableDefinition()
  {
    $this->setTableName('media');
    $this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'sequence' => 'media_id'));
    $this->hasColumn('user_id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'notnull' => true));
    $this->hasColumn('session_id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'notnull' => true));
    $this->hasColumn('media_type_id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'notnull' => true));
    $this->hasColumn('state_id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'notnull' => true));
    $this->hasColumn('media_id', 'string', null, array('type' => 'string', 'notnull' => true));
  }

    public function setUp() {
    $this->hasMany('MediumVideo', array('local' => 'id',
                                'foreign' => 'media_id'));
  }

}