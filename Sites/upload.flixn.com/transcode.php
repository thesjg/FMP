<?php

require(dirname(__FILE__) . '/global.php');

/* XXX: */
$sourceLocation = '/z/media/incoming';
$targetLocation = '/z/media/fmp';
$command = '/z/www/upload.flixn.evilprojects.net/transcode/transcode_h264.py';
$s3cmd = '/z/www/upload.flixn.evilprojects.net/FxS3/fxs3.py';


$job = new FlixnDatabaseTranscodeJobs();
if ($job->loadOne("processing='f'") === false)
    exit();

$fdm = new FlixnDatabaseMedia();

$fdm->load($job->media_id);

$media_id = $fdm->media_id;
$loc_suffix = $media_id{19} . '/' . $media_id{20} . '/' . $media_id{21} . '/'
            . $media_id{22} . '/' . $media_id . '/';
$sl = $sourceLocation . '/' . $loc_suffix;
$tl = $targetLocation . '/' . $loc_suffix;

mkdir($tl);
chdir($tl);

$lockfp = fopen($tl . '.lock', 'w+');

if (flock($lockfp, LOCK_EX | LOCK_NB)) { // do an exclusive lock

    $job->processing = 't';
    $job->save();

    $fdtv = new FlixnDatabaseTranscodeVideo();
    $fdta = new FlixnDatabaseTranscodeAudio();
    $fdmvf = new FlixnDatabaseMediaVideoFormats();

    $fdtv->load($job->video_id);
    $fdmvf->load($fdtv->media_video_format);

    if (!$fdtv->audio_id == NULL) {
        $fdta->load($fdtv->audio_id);
        $audio_bitrate = $fdta->bitrate;
    } else {
        $audio_bitrate = 0;
    }

    if (!$fdtv->framerate == NULL) {
        $fdtvf = new FlixnDatabaseTranscodeVideoFramerates();
        $fdtvf->load($fdtv->framerate);
        $framerate = $fdtvf->framerate;
    } else {
        $framerate = 0;
    }

    $sourceFile = $sl . 'original';
    $targetFileTmp = $tl . '.' . str_replace(' ', '_', $fdtv->name) . '.' . strtolower($fdmvf->format);
    $targetFile = $tl . str_replace(' ', '_', $fdtv->name) . '.' . strtolower($fdmvf->format);

    $cmd_format = '%s -o %s -i %s -x %s -y %s -f %s -v %s -a %s';

    $cmd = sprintf($cmd_format, $command, $targetFileTmp, $sourceFile,
                   $fdtv->width, $fdtv->height, $framerate,
                   $fdtv->bitrate, $audio_bitrate);

    print "\n$cmd\n";
    exec($cmd);

    $cmd = sprintf('/usr/local/bin/qt-faststart %s %s', $targetFileTmp, $targetFile);

    print "\n$cmd\n";
    exec($cmd);

    unlink($targetFileTmp);

    $im = new IdentifyMedia($targetFile);
    $media_info = $im->getMediaInfo();

    $fi = new FlixnIdentification();
    $ex_ident = (int)$fi->UUIDExtractIdent($media_id);

    $jobs = $job->query("SELECT id FROM transcode_jobs WHERE media_id='$job->media_id' ORDER BY id");
    $i = 1;
    for (; $i <= $jobs->rowCount(); $i++) {
        $x_job = $jobs->fetch();
        if ($x_job['id'] == $job->id)
            break;
    }
    if ($i == $jobs->rowCount())
        $job->query("DELETE FROM transcode_jobs WHERE media_id='$job->media_id'");

    print "XXX: I: " . $i . "\n";
    print "XXX: EX_IDENT: " . $ex_ident . "\n";
    print "XXX: " . ($ex_ident + ($i * 10)) . "\n";
    $ex_new_ident = substr('0000' . (string)($ex_ident + ($i * 10)), -4);
    print "XXX: EX_NEW_IDENT: " . $ex_new_ident . "\n";

    $media_video_id = str_replace('0001', $ex_new_ident, $media_id);
    //$media_video_id = $media_id; // XXXXXXX XXX XXX XXX will overwrite for each

    $fdmv = new FlixnDatabaseMediaVideo();
    $fdmv->media_id = $job->media_id;
    $fdmv->transcode_id = $fdtv->id;
    $fdmv->original = 'f';
    $fdmv->audio = 't';
    $fdmv->size = $media_info['file_size'];
    $fdmv->duration = $media_info['duration'];
    $fdmv->media_video_id = $media_video_id;
    $fdmv->save();

    flock($lockfp, LOCK_UN);
    fclose($lockfp);


// XXX: This s3 upload stuff should go elsewhere

    $fdsus = new FlixnDatabaseStorageUserSettings();
    if ($fdsus->loadBy('user_id', $fdm->user_id)) {

        // S3 == 2
        if ($fdsus->storage_class_id == 2) {

            $fduss3 = new FlixnDatabaseStorageUserSettingsS3();
            if ($fduss3->loadBy('user_id', $fdm->user_id)) {

                if ($fduss3->bucket == NULL)
                    $bucket = 'flixnmedia';

                if ($fduss3->token == NULL)
                    $token = NULL;

                $fdsms3 = new FlixnDatabaseStorageMetaS3();
                if (!$fdsms3->loadBy('media_x_uuid', $media_video_id)) {

                    $fdsms3->media_x_uuid = $media_video_id;
                    $fdsms3->save();

                    $s3args = " -c media-store -b $bucket -k $media_video_id." . strtolower($fdmvf->format);
                    $s3args .= " -f $targetFile";

                    if ($token !== NULL)
                        $s3args .= ' -t "' . $token . '"';

                    echo system($s3cmd . $s3args, $s3ulret);

                    if ($s3ulret == 0) {
                        $fdsms3->uploading = 'F';
                        $fdsms3->available = 'T';
                        $fdsms3->save();

                        $fdmv->storage_class_id = 2;
                        $fdmv->save();

                        unlink($targetFile);
                    } else {
                        echo "XXX S3: Upload failed! MEDIA_VIDEO_ID: " . $media_video_id . "\n";
                        $fdsms3->delete();
                    }
                }
            }
        }
    }
}
