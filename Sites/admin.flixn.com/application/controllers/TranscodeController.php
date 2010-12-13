<?php

require_once 'Zend/Controller/Action.php';

class TranscodeController extends Zend_Controller_Action {

    private $identity;

    public function init() {
        $auth = Zend_Auth::getInstance();
        $this->identity = $auth->getIdentity();

        if (!$this->identity)
            $this->_redirect('/auth/login');

        $this->view->BaseUrl = $this->_request->getBaseUrl();
        $this->view->Controller = $this->_request->getParam('controller');
        $this->view->Path = $this->_request->getPathInfo();

        /*$this->_getModerationInstances();*/
    }

    public function createAction()
    {
        $this->view->typeName = "Transcode Profile";
        $this->_helper->layout->disableLayout();
        $ident = new FlixnIdentification();
        $userid = $this->identity['id'];

        $video_options = Doctrine::getTable('MediumVideoFormat')->findAll();
        $this->view->videoOptions = $video_options;

        // XXX - use doctrine or at least some global connection class
        $link = pg_connect("host=db.flixn.com port=5432 dbname=fmp_dev user=flixn");

        $query = "SELECT ma.format, ta.id, ta.bitrate FROM media_audio_formats ma,
                    transcode_audio ta WHERE ta.media_audio_format = ma.id";

        $audio_options = pg_fetch_all(pg_query($link, $query));
        //foreach($audio_options)

        $framerate_options = Doctrine::getTable('TranscodeVideoFramerate')
                        ->findAll();

        $this->view->framerate_options = $framerate_options;

        $this->view->audioOptions = $audio_options;

        if ($this->_request->isPost())
        {
            $postdata = $this->_request->getPost();
            $mvf = new TranscodeVideo();
            $mvf['user_id'] = $userid;
            $mvf['name'] = $postdata['instance_name'];
            $mvf['media_video_format'] = $postdata['video_format'];
            $mvf['bitrate'] = (int)$postdata['bitrate'] * 1024;

            $mvf['audio_id'] = $postdata['audio_format'];

            if($postdata['audio_format'] == "null")
                $mvf['audio_id'] = null;
            else
                $mvf['audio_id'] == $postdata['audio_format'];

            $mvf['width'] = $postdata['width'];
            $mvf['height'] = $postdata['height'];

            if ($postdata['framerate'] == "null")
                $mvf['framerate'] = null;
            else
                $mvf['framerate'] == $postdata['framerate'];

            $mvf->save();
            //XXX MEGA SHITTY CODE ALERT
            
            $mvf_new = Doctrine_Query::create()
                    ->select('id')
                    ->from('TranscodeVideo')
                    ->where('user_id = ?', $userid)
                    ->addWhere('name =?', $mvf['name'])->execute();
            
            $mvf_id = $mvf_new[0]['id'];
            
            $players = Doctrine_Query::create()
		    ->select('id')
		    ->from('ComponentInstance')
		    ->Where('user_id = ?', $userid)
		    ->execute();
                    
            $transcode_count = Doctrine_Query::create()
                        ->select('id, name')
                        ->from('TranscodeVideo tv, TranscodePriority tp')
                        ->where('tp.transcode_id = tv.id')
                        ->addWhere('tv.user_id = ?', $userid)
                        ->addWhere('tp.component_id = ?', $players[0]['id'])
                        ->orderby('tp.priority')
                        ->execute();
            
            
            $priority = $transcode_count->count();
            foreach ($players as $p) {
                $tsetup = new TranscodePriority();
                $tsetup['priority'] = $priority;
                $tsetup['component_id'] = $p['id'];
                $tsetup['transcode_id'] = $mvf_id;
                $tsetup->save();
            }
            
            $this->_redirect('/transcode/');
        }
    }

    public function ajaxAction()
    {
        $ident = new FlixnIdentification();
        $userid = $this->identity['id'];
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $profile_id = $this->_request->getParam('profile');

        if ($this->_request->isXmlHttpRequest())
        {
            $video = Doctrine::getTable('TranscodeVideo')
                        ->findOneById($profile_id);
             if (!$video)
                exit();
            elseif ($video['user_id'] != $userid)
                exit();

            $video['width'] = $_POST['width'];
            $video['height'] = $_POST['height'];
            $video['framerate'] = $_POST['framerate'];
            if($_POST['framerate'] == "null")
                $video['framerate'] = null;

            $video['bitrate'] = (int)ceil($_POST['bitrate'] * 1024);
            $video['media_video_format'] = $_POST['video_format'];

            if($_POST['audio_format'] == "null")
                $video['audio_id'] == null;
            else
              $video['audio_id'] = $_POST['audio_format'];


            $video->save();
            print("Your settings have been successfully updated.");
        }
    }

    public function settingsAction()
    {

        $ident = new FlixnIdentification();
        $userid = $this->identity['id'];

        $profile_id = $this->_request->getParam('profile');
        $this->view->profile_id = $profile_id;

        $video = Doctrine::getTable('TranscodeVideo')->findOneById($profile_id);

        if (!$video)
            $this->_redirect('/transcode');
        elseif ($video['user_id'] != $userid)
            $this->_redirect('/transcode');

        $framerate_options = Doctrine::getTable('TranscodeVideoFramerate')->findAll();



        $framerate_opts = array();
        foreach($framerate_options as $f)
            {
                $checked = '';

                if ($f['id'] == $video['framerate'])
                    $checked = "selected='selected'";
                else
                    $checked = '';

                $framerate_opts[] = array('framerate' => $f['framerate'], 'id' => $f['id'], 'checked' => $checked);
            }

        $this->view->framerate_options = $framerate_opts;

        $video_options = Doctrine::getTable('MediumVideoFormat')->findAll();

        $video_opts = array();
        foreach($video_options as $v)
            {
                $checked = '';

                if ($v['id'] == $video['media_video_format'])
                    $checked = "selected='selected'";
                else
                    $checked = '';

                $video_opts[] = array('format' => $v['format'], 'id' => $v['id'], 'checked' => $checked);
            }


        $this->view->videoOptions = $video_opts;
        $link = pg_connect("host=db.flixn.com port=5432 dbname=fmp_dev user=flixn");

        $query = "SELECT ma.format, ta.id, ta.bitrate FROM media_audio_formats ma,
                    transcode_audio ta WHERE ta.media_audio_format = ma.id";

        $audio_options = pg_fetch_all(pg_query($link, $query));

        $audio_opts = array();
        foreach($audio_options as $a)
            {
                $checked = '';

                if ($a['id'] == $video['audio_id'])
                    $checked = "selected='selected'";
                else
                    $checked = '';

                $audio_opts[] = array('format' => $a['format'], 'id' => $a['id'], 'bitrate' => $a['bitrate'], 'checked' => $checked);
            }

        $this->view->audioOptions = $audio_opts;
        $this->view->p_width = $video['width'];
        $this->view->p_height = $video['height'];
        $this->view->p_fps = $video['framerate'];
        $this->view->v_bitrate = $video['bitrate']/1024;
        $this->view->p_name = $video['name'];
    }

    public function indexAction()
    {
        $ident = new FlixnIdentification();
        $userid = $this->identity['id'];

        $link = pg_connect("host=db.flixn.com port=5432 dbname=fmp_dev user=flixn");

        $query = "SELECT tv.id, (tv.bitrate/1024) as vbitrate, tv.width, tv.height, tv.name,
        maf.format as aformat, tvf.framerate, mvf.format as vformat, (ta.bitrate/1024) as abitrate
        FROM transcode_video tv
        LEFT JOIN  media_video_formats mvf
        ON (mvf.id = tv.media_video_format)
        LEFT JOIN transcode_audio ta LEFT JOIN media_audio_formats maf ON (maf.id = ta.media_audio_format)
        ON (ta.id = tv.audio_id)
        LEFT JOIN transcode_video_framerates tvf
        ON (tvf.id = tv.framerate) WHERE user_id = $userid;";

        $objects = pg_fetch_all(pg_query($link, $query));

        $this->view->transcodeObjects = $objects;
    }


    //
    //private function _checkInstance() {
    //    $instance_id = $this->_request->getParam('instance');
    //    if (!is_numeric($instance_id))
    //        $this->_redirect('/' . $this->_request->getParam('controller') . '/index');
    //
    //    $instances = Doctrine_Query::create()
    //                 ->select('id,name')
    //                 ->from('ComponentType ct, ComponentInstance ci')
    //                 ->where("ct.name=:name AND ci.user_id=:user_id AND ci.type_id=ct.id",
    //                         array(':name' => 'recorder',
    //                               ':user_id' => $this->identity['id']))
    //                 ->count();
    //
    //    if (count($instances) > 0) {
    //        return true;
    //    } else {
    //        $this->_redirect('/' . $this->_request->getParam('controller') . '/index');
    //    }
    //}
    //
    //private function _getModerationInstances()
    //{
    //    $instances = Doctrine::getTable('ModerationInstance')
    //                ->findByUserId($this->identity['id']);
    //    $this->view->moderationInstances = $instances;
    //}
}