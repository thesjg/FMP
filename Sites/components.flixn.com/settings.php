<?php

$settings_tpl = <<<TPL
<?xml version="1.0" encoding="UTF-8"?>
<settings>
 <id>%s</id>
 <key>%s</key>
 <style>%s</style>
</settings>
TPL;

require('global.php');

$fdci = new FlixnDatabaseComponentInstances();
if (!$fdci->loadByComponentId($_GET['cid']))
    do_error('Could not retrieve component instance');

$fdct = new FlixnDatabaseComponentTypes();
if (!$fdct->loadBy('id', $fdci->type_id))
    do_error('Could not retrieve component type');

require('settings/' . $fdct->name . '/' . $fdct->version . '.php');