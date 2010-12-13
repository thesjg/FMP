<?php

// XXX
//require_once('global.php');

//create_transcode_jobs(0, 0, 0);

function create_video_transcode_jobs($user_id, $media_db_id, $width, $height)
{

    // XXX: Over/Under threshold, 4:3 = 1.3, 16:9 = 1.8
    $outhr = 1.5;

    // XXX: Pixel density threshold, only encode to a format if we are within 20%
    $pdthr = 1.2;

    $pixel_density = $width * $height;
    $over_under = (($width / $height) > $outhr) ? 'over' : 'under';


    $fdtv = new FlixnDatabaseTranscodeVideo();
    $result = $fdtv->loadAll("user_id='$user_id'");

    $last = NULL;
    $targets = array();
    while ($row = $result->fetch()) {

        $m_width = (int)$row['width'];
        $m_height = (int)$row['height'];

        $m_pd = $m_width * $m_height;
        $m_ou = (($m_width / $m_height) > $outhr) ? 'over' : 'under';

        if ($over_under != $m_ou) {
//            print "Over/Under condition not met, skipping\n";
            continue;
        }

        $last = $row;
        if (($pixel_density * $pdthr) < $m_pd) {
//            print "Not within pixel density threshold, skipping\n";
            continue;
        }

        $targets[] = $row;
        //print_r($row);
    }

    // XXX: UNLESS THE MEDIA ALREADY A VALID FLV
    // Always produce at least 1 transcode target
    if (count($targets) == 0)
        $targets[] = $last;

    foreach ($targets as $target) {
        $fdtj = new FlixnDatabaseTranscodeJobs();
        $fdtj->media_id = $media_db_id;
        $fdtj->video_id = $target['id'];
        $fdtj->save();
    }
}

function create_audio_transcode_jobs()
{

}

function create_image_transcode_jobs()
{

}