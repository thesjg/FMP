<?php

require_once '../../api.flixn.evilprojects.net/Flixn/Identification.php';

$zend_path = dirname(__FILE__) . '/../';
set_include_path('.'
            . PATH_SEPARATOR . dirname(__FILE__) . '/../library'
            . PATH_SEPARATOR . dirname(__FILE__) . '/../library/Doctrine'
            . PATH_SEPARATOR . dirname(__FILE__) . '/../library/Flixn'
            . PATH_SEPARATOR . dirname(__FILE__) . '/../application/models'
            . PATH_SEPARATOR . dirname(__FILE__) . '/../application/models/generated'
            . PATH_SEPARATOR . dirname(__FILE__) . '/../application/views/helpers');

require_once 'Zend/Loader.php';
Zend_Loader::registerAutoload();

//provide specifically for statistics query module
$config = new Zend_Config_Ini('../application/config.ini', 'general');
$registry = Zend_Registry::getInstance();
$registry->set('config', $config);

$db = Zend_Db::factory($config->db);
Zend_Db_Table::setDefaultAdapter($db);

//Provide for general connectivity
Doctrine_Manager::connection('pgsql://flixn:@db.flixn.com/fmp_dev');

$fc = Zend_Controller_Front::getInstance();
$fc->setControllerDirectory('../application/controllers');
$fc->throwExceptions(true);
Zend_Layout::startMvc(array('layoutPath'=>'../application/layouts'));

$fc->dispatch();
