<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class MediumVideo extends BaseMediumVideo
{


  public function setUp() {
    $this->hasOne('Medium', array('local' => 'media_id',
                            'foreign' => 'id'));
  }
}