<?php

require('global.php');

if (!isset($_GET['tid']))
    do_error('Ticket identifier not specified');

$ticketId = $_GET['tid'];
$fieldName = 'Filedata';
$targetLocation = '/z/media/incoming';

if (isset($_FILES[$fieldName]))
    $file = $_FILES[$fieldName];
else
    do_error('No file data provided');

$im = new IdentifyMedia($file['tmp_name']);
$media_type = $im->getMediaType();
if (!in_array($media_type, array('video')))
    do_error('Supplied file is not of a valid or recognized type or format');
//if (!in_array($media_type, array('video', 'audio', 'image')))
//    do_error('Supplied file is not of a valid or recognized type or format');

$media_info = $im->getMediaInfo();

$fdat = new FlixnDatabaseAPITickets();
if (!$fdat->loadByTicketId($ticketId))
    do_error('Could not retrieve ticket');

// XXX: Verify file size limits

$fdci = new FlixnDatabaseComponentInstances();
if (!$fdci->loadBy('id', $fdat->component_instance_id))
    do_error('Could not retrieve component instance');

$fdmt = new FlixnDatabaseMediaTypes();
if (!$fdmt->loadByName($media_type))
    do_error();

// XXX: Media state id = 1 (received)

$fi = new FlixnIdentification();
$media_id = $fi->UUIDGenerate(1); // XXX: Hardcoded cluster id


$td = $targetLocation . '/' . $media_id{19} . '/' . $media_id{20}
    . '/' . $media_id{21} . '/' . $media_id{22} . '/' . $media_id;

mkdir($td);

if (!move_uploaded_file($file['tmp_name'], $td . '/original'))
    do_error('General error uploading file');


$fdm = new FlixnDatabaseMedia();
$fdm->user_id = $fdci->user_id;
$fdm->session_id = $fdat->session_id;
$fdm->media_type_id = $fdmt->id;
$fdm->state_id = 1;
$fdm->media_id = $media_id;
$fdm->save();

$fdm->loadByMediaId($fdm->media_id);

$fdmc = new FlixnDatabaseMediaMetadataCreation();
$fdmc->media_id = $fdm->id;
$fdmc->url = $_POST['creation_url'];
$fdmc->component_id = $fdat->component_instance_id;
$fdmc->save();

$fdmmi = new FlixnDatabaseMediaMetadataInternal();
$fdmmi->media_id = $fdm->id;
$fdmmi->key = 'filename';
$fdmmi->value = $_POST['Filename'];
$fdmmi->save();

foreach ($media_info as $key => $value) {
    $fdmmi = new FlixnDatabaseMediaMetadataInternal();
    $fdmmi->media_id = $fdm->id;
    $fdmmi->key = $key;
    $fdmmi->value = $value;
    $fdmmi->save();
}

switch ($media_type) {
    case 'video':
        $fdmv = new FlixnDatabaseMediaVideo();
        $fdmv->media_id = $fdm->id;
        $fdmv->transcode_id = NULL;
        $fdmv->original = 'T';
        $fdmv->audio = (isset($media_info['audio_codec'])) ? 'T' : 'F';
        $fdmv->size = $media_info['file_size'];
        $fdmv->duration = $media_info['duration'];
        $fdmv->save();

        create_video_transcode_jobs($fdci->user_id, $fdm->id,
                                    $media_info['video_width'],
                                    $media_info['video_height']);
        break;
    case 'audio':
        break;
    case 'image':
        break;
}

$fdmc = new FlixnDatabaseModerationComponents();
$result = $fdmc->loadAll("component_instance_id=$fdat->component_instance_id");
if ($result->rowCount() > 0) {
    $fdms = new FlixnDatabaseModerationStates();
    $fdms->loadBy('name', 'PENDING');
    while ($row = $result->fetch()) {
        $fdmi = new FlixnDatabaseModerationInstances();
        $fdmi->load($row['instance_id']);
        if ($fdmi->deferred == false) {
            $fdmm = new FlixnDatabaseModerationMedia();
            $fdmm->media_id = $fdm->id;
            $fdmm->state_id = $fdms->id;
            $fdmm->save();
        }
    }
}

do_result($fdm->media_id);