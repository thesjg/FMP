<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class BaseComponentPlayerStyleOption extends Doctrine_Record
{
  public function setTableDefinition()
  {
    $this->setTableName('component_player_style_options');
    $this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'sequence' => 'component_player_style_options_id'));
  }

}