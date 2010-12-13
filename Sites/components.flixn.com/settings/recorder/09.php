<?php

$fdcrs = new FlixnDatabaseComponentRecorderSettings();
$fdcrs->loadByInstanceId($fdci->id);

printf($settings_tpl, $_GET['cid'], $fdci->component_key,
       ($fdcrs->video) ? 'video' : 'audio');