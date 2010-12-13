<?php
/**
 * Flixn
 *
 * @category    Flixn
 * @package     Services
 *
 * @author      Samuel J. Greear
 * @copyright   Copyright (c) 2008 Flixn, Inc.
 * @version     $Id $
 */

class FlixnServicesMedia extends FlixnServices
{

    /**
     * XXX
     *
     * @param string $sessionId
     * @param string $mediaId
     * @param string $protocol
     *
     * @return FlixnServicesMediaMedia media
     */
    public function getMedia($sessionId, $mediaId, $protocol='http')
    {
        $this->validateSession($sessionId);

        $m = new FlixnServicesMediaMedia();
        $m->id       = $mediaId;
        $m->protocol = $protocol;

if (strlen($mediaId) > 20) {
        $moderation_status = $this->_getModerationStatus($mediaId);
        if ($moderation_status !== false && $moderation_status != 'APPROVED') {
            $m->status = 'MODERATION';
// XXX: NO, BAD, figure out another way for moderation in the panel to work!
//            return $m;
        }

        $job_count = $this->_getTranscodeJobCount($mediaId);
        if ($job_count > 0 && !($m->status == 'MODERATION'))
            $m->status = 'TRANSCODE';

        $targets = $this->_getTargets($sessionId, $mediaId);
        if (count($targets) > 0 && !($m->status == 'MODERATION'))
            $m->status = 'ACTIVE';
//        else
//            return $m;
}

        $locations = $this->_getLocations($sessionId, $mediaId, $protocol);
        $ml = new FlixnServicesMediaLocation();
        $ml->name = 'Master';
        $ml->hostname = $locations[0];

        $m->locations = array($ml);

        if (strlen($mediaId) < 20) {
            $ma = new FlixnServicesMediaAsset();
            $ma->target = 'Recorded FLV';
            $ma->format = 'flv';
            $ma->path = '/' . $mediaId{0} . '/' . $mediaId{1} . '/'
                      . $mediaId{2} . '/' . $mediaId . '.' . $ma->format;
            $m->assets[] = $ma;

            return $m;
        }

        $m->assets = array();

        if ($ml->hostname == 'media.flixn.com') {
            foreach ($targets as $target) {
                $ma = new FlixnServicesMediaAsset();
                $ma->target = $target['name'];
                $ma->format = 'mp4';

                $ma->path = '/' . $mediaId{19} . '/' . $mediaId{20} . '/'
                          . $mediaId{21} . '/' . $mediaId{22} . '/'
                          . $mediaId . '/' . str_replace(' ', '_', $target['name'])
                          . '.' . $ma->format;

                $m->assets[] = $ma;
            }
        } else {
            // XXX: S3

            foreach ($targets as $target) {
                $ma = new FlixnServicesMediaAsset();
                $ma->target = $target['name'];
                $ma->format = 'mp4';

                $ma->path = '/' . $target['vid']. '.' . $ma->format;

                $m->assets[] = $ma;
            }
        }

        return $m;
    }

    /**
     * XXX
     *
     * @param string $sessionId
     * @param string $mediaId
     * @param string $phoneNumber
     *
     * @return boolean success
     */
    public function smsMedia($sessionId, $mediaId, $phoneNumber)
    {
        // XXX: Validate that they do indeed have SMS turned on for the players sessionId
        // XXX: Blatantly assume this video is on legacy

        if (strlen($mediaId) > 20)
            return false;

//        $this->validateSession($sessionId);

        $media_url = 'http://legacymedia.flixn.com/' . $mediaId{0} . '/' . $mediaId{1} . '/'
                      . $mediaId{2} . '/' . $mediaId . '.3gp';

        $clickatell_url = 'http://api.clickatell.com/http/sendmsg?user=username&password=password&api_id=12345&to=%s&text=%s';
        $clickatell_url = sprintf($clickatell_url, $phoneNumber, urlencode($media_url));

        $ch = curl_init($clickatell_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);

        return true;
    }

    /**
     * XXX
     *
     * @param string $sessionId
     * @param string $componentId
     * @param string $mediaId
     * @param string $toAddress
     * @param string $fromName
     * @param string $message
     *
     * @return boolean success
     */
	public function emailMedia($sessionId, $componentId, $mediaId, $toAddress, $fromName, $message)
	{
		$url = 'http://components.flixn.com/support/player/popout/' . $componentId . '/' . $mediaId;
		
		$fsme = new FlixnServicesMediaEmail();
		$fsme->Send($fromName, $toAddress, $message, $url);
		
		return true;
	}

    public function getMediaMetadata($sessionId, $mediaId)
    {

    }

    private function _getLocations($sessionId, $mediaId, $protocol)
    {
        if (strlen($mediaId) < 20)
            return array('legacymedia.flixn.com');

        $fdm = new FlixnDatabaseMedia();
        $fdm->loadByMediaId($mediaId);

        // XXX: Video
        if ($fdm->media_type_id == 2) {
            $fdmv = new FlixnDatabaseMediaVideo();

            $res = $fdmv->loadAll("media_id=$fdm->id AND storage_class_id=2");
            if ($res->rowCount() > 0) {

                $fdsuss3 = new FlixnDatabaseStorageUserSettingsS3();
                $fdsuss3->loadBy('user_id', $fdm->user_id);

                $bucket = 'flixnmedia';
                if ($fdsuss3->bucket != NULL)
                    $bucket = $fdsuss3->bucket;

                $url = 's3.amazonaws.com/' . $bucket;
                return array($url);
            }
        }

        return array('media.flixn.com');
    }

    private function _getTargets($sessionId, $mediaId)
    {
        $fd = new FlixnDatabase();

        $result = $fd->query(sprintf("SELECT DISTINCT name, vid, prio FROM
                    (SELECT
                        tv.name AS name,
                        mv.media_video_id AS vid,
                        tp.priority AS prio
                    FROM
                        transcode_video tv,
                        transcode_priority tp,
                        media_video mv,
                        media m
                    WHERE
                        tv.id=tp.transcode_id
                    AND
                        mv.transcode_id=tv.id
                    AND
                        mv.media_id=m.id
                    AND
                        m.media_id='%s'
                    ORDER BY
                        tp.priority ASC) AS sq ORDER BY prio", $mediaId));

        $targets = array();

        while ($row = $result->fetch())
            $targets[] = array('name' => $row['name'],
                               'vid' => $row['vid']);

        if (count($targets) == 0) {
            $result = $fd->query(sprintf("SELECT
                            tv.name AS name,
                            mv.media_video_id AS vid
                        FROM
                            transcode_video tv,
                            media_video mv,
                            media m
                        WHERE
                            mv.transcode_id=tv.id
                        AND
                            mv.media_id=m.id
                        AND
                            m.media_id='%s'
                        ORDER BY
                            tv.bitrate DESC", $mediaId));

            while ($row = $result->fetch())
                $targets[] = array('name' => $row['name'],
                                   'vid' => $row['vid']);
        }

        return $targets;
    }

    private function _getTranscodeJobCount($mediaId)
    {
        $fdm = new FlixnDatabaseMedia();
        if (!$fdm->loadBy('media_id', $mediaId))
            return false;

        $fdtj = new FlixnDatabaseTranscodeJobs();
        $tjs = $fdtj->loadAll("media_id=$fdm->id");

        return $tjs->rowCount();
    }

    private function _getModerationStatus($mediaId)
    {
        $fdm = new FlixnDatabaseMedia();
        if (!$fdm->loadBy('media_id', $mediaId))
            return false;

        $fdmm = new FlixnDatabaseModerationMedia();
        if (!$fdmm->loadBy('media_id', $fdm->id))
            return false;

        $fdms = new FlixnDatabaseModerationStates();
        $fdms->load($fdmm->state_id);

        return $fdms->name;
    }
}
