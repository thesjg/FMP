#!/bin/env php
<?php

require_once(dirname(__FILE__) . '/../library/Doctrine/Doctrine.php');

spl_autoload_register(array('Doctrine', 'autoload'));
$conn = Doctrine_Manager::connection('pgsql://flixn:@db.flixn.com/fmp_dev');

$cons = $conn->import->listTableConstraints('component_instances');
print_r($cons);
echo "\n";

// import method takes one parameter: the import directory (the directory where
// the generated record files will be put in
Doctrine::generateModelsFromDb(dirname(__FILE__) . '/../application/models');
