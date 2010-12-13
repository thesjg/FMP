<?php

require(dirname(__FILE__) . '/../api.flixn.evilprojects.net/Ex/Global.php');

require('identify_media.php');
require('transcode_jobs.php');

function do_error($desc) {
$err = <<<ERR
<?xml version="1.0" encoding="UTF-8"?>
<error code="0000">%s</error>
ERR;

    printf($err, $desc);
    exit();
}

function do_result($media_id) {
$res = <<<RES
<?xml version="1.0" encoding="UTF-8"?>
<mediaid>%s</mediaid>
RES;

    printf($res, $media_id);
    exit();
}