<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class BaseTranscodeJob extends Doctrine_Record
{
  public function setTableDefinition()
  {
    $this->setTableName('transcode_jobs');
    $this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'sequence' => 'transcode_jobs_id'));
    $this->hasColumn('media_id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'notnull' => true));
    $this->hasColumn('video_id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'notnull' => true));
    $this->hasColumn('processing', 'boolean', 1, array('type' => 'boolean', 'length' => 1, 'notnull' => true, 'default' => 'false'));
  }

}