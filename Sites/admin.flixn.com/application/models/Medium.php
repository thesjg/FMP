<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Medium extends BaseMedium
{
    
  public function setUp() {
    $this->hasMany('MediumVideo', array('local' => 'id',
                        'foreign' => 'media_id'));
  }

}