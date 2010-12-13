<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class BaseTranscodeComponentAudio extends Doctrine_Record
{
  public function setTableDefinition()
  {
    $this->setTableName('transcode_component_audio');
    $this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'sequence' => 'transcode_component_audio_id'));
    $this->hasColumn('component_id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'notnull' => true));
    $this->hasColumn('audio_id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'notnull' => true));
  }

}