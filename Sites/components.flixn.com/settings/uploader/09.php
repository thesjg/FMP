<?php

$fdcus = new FlixnDatabaseComponentUploaderSettings();
$fdcus->loadByInstanceId($fdci->id);

printf($settings_tpl, $_GET['cid'], $fdci->component_key,
       ($fdcus->single) ? 'single' : 'multiple');