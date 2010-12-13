<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class BaseLanguage extends Doctrine_Record
{
  public function setTableDefinition()
  {
    $this->setTableName('languages');
    $this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'sequence' => 'languages_id'));
    $this->hasColumn('country_id', 'integer', 8, array('type' => 'integer', 'length' => 8));
    $this->hasColumn('code', 'string', 63, array('type' => 'string', 'length' => '63', 'notnull' => true));
    $this->hasColumn('name', 'string', 63, array('type' => 'string', 'length' => '63', 'notnull' => true));
  }

}