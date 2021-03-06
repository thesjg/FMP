<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class BaseTranscodeVideoDefault extends Doctrine_Record
{
  public function setTableDefinition()
  {
    $this->setTableName('transcode_video_defaults');
    $this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'sequence' => 'transcode_video_defaults_id'));
    $this->hasColumn('media_video_format', 'integer', 8, array('type' => 'integer', 'length' => 8, 'notnull' => true));
    $this->hasColumn('audio_id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'notnull' => true));
    $this->hasColumn('width', 'integer', 4, array('type' => 'integer', 'length' => 4, 'notnull' => true));
    $this->hasColumn('height', 'integer', 4, array('type' => 'integer', 'length' => 4, 'notnull' => true));
    $this->hasColumn('framerate', 'integer', 4, array('type' => 'integer', 'length' => 4, 'notnull' => true));
    $this->hasColumn('bitrate', 'integer', 4, array('type' => 'integer', 'length' => 4, 'notnull' => true));
    $this->hasColumn('name', 'string', 63, array('type' => 'string', 'length' => '63', 'notnull' => true));
    $this->hasColumn('priority', 'integer', 4, array('type' => 'integer', 'length' => 4, 'notnull' => true));
  }

}