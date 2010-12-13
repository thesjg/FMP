<?php

    require_once 'Zend/Controller/Action.php';

class RecordedController extends Zend_Controller_Action {

    private $identity;

    public function init() {
        $auth = Zend_Auth::getInstance();
        $this->identity = $auth->getIdentity();

        if (!$this->identity) {
            $this->_redirect('/auth/login');
        }

        $this->view->BaseUrl = $this->_request->getBaseUrl();
        $this->view->Controller = $this->_request->getParam('controller');
        $this->view->Path = $this->_request->getPathInfo();


        $this->_getModerationInstances();
    }

    public function indexAction() {
        //display documentation
        $instance_id = $this->_request->getParam('instance');
        $redirect = $this->_request->getParam('instance');
        $action = $this->_request->getParam('action');

        if(isset($instance_id) && is_numeric($instance_id) && isset($redirect) && $action = "index")
        {
            $this->_redirect('/recorded/settings/instance/' . $instance_id);
        }


    }

    public function createAction() {
        $this->_helper->layout->disableLayout();
        $this->view->typeName = "Recorder";
        
        $ident = new FlixnIdentification();
        $userid = $this->identity['id'];
        
        $formData = $this->_request->getPost();

        if ($this->_request->isPost()) {

            $exists = Doctrine_Query::create()
                ->select('ci.id')
                ->from('ComponentInstance ci, ComponentType ct')
                ->where('ct.name = \'recorder\'')
                ->addWhere('ci.type_id =  ct.id')
                ->addWhere('ci.name = \''. $formData['name'].'\'')
                ->addWhere('ci.user_id= '. $userid)
                ->distinct()
                ->execute();

                $this->_helper->viewRenderer->setNoRender();

                if (is_numeric($exists[0]['id'])) {
                    print("0:" . $exists[0]['id']);
                }
                else
                {

                $type = Doctrine::getTable('ComponentType')
                        ->findOneByName('recorder');

                $ci = new ComponentInstance();

                $ci['user_id'] = $this->identity['id'];
                $ci['type_id'] = $type['id'];
                $ci['component_id'] = $ident->UUIDGenerate(1);
                $ci['component_key'] = $ident->UUIDKeyGenerate();
                $ci['name'] = $formData['name'];
                $ci->save();

                $crset = new ComponentRecorderSetting();
                $crset['instance_id'] = $ci['id'];
                $crset['video'] = true;
                $crset['high_quality'] = true;
                $crset['time_limit'] = 300;
                //default 5 min
                $crset['high_quality'] = true;
                $crset['style_id'] = 1;

                //$crset['moderated'] = false;
                //"ModerationComponent" table lists usage

                $crset->save();
        
                print("1:" . $ci['id']);

            }

        }
    }

    public function ajaxAction() {

        $ident = new FlixnIdentification();
        $userid = $this->identity['id'];
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $this->_checkInstance();
        $instance_id = $this->_request->getParam('instance');

        if ($this->_request->isXmlHttpRequest()) {
            switch ($_POST['formMode']) {
            case "settings":
                // use superglobal request, <> _POST
                $ci = new ComponentInstance();
                $ci = Doctrine::getTable('ComponentInstance')
                    ->findOneById($instance_id);

                $this->view->instanceName = $ci['name'];

                if ($ci['name'] != $_POST['s_name']) {
                    $ci['name'] = $_POST['s_name'];
                    $ci->save();
                }

                $crs = Doctrine::getTable('ComponentRecorderSetting')
                    ->findOneByInstanceId($instance_id);

                if ($_POST['s_type'] == 'video') {
                    $crs->video = true;
                } else {
                    $crs->video = false;
                }

                if ($_POST['s_hq'] == 'enable') {
                    $crs->high_quality = true;
                } else {
                    $crs->high_quality = false;
                }

                //arbitrary time limit - solve?
                $time_limit = ($_POST['s_time_hours'] * 3600) +
                              ($_POST['s_time_minutes'] * 60) +
                              $_POST['s_time_seconds'];

                if ($time_limit > (4*3600)) {
                    $crs->time_limit = (4*3600);
                } else {
                    $crs->time_limit = $time_limit;
                }

                if($_POST['s_transcode_enable'] == "enable")
                    $crs->transcode_profile = $_POST['s_transcode_profile'];
                else
                    $crs->transcode_profile = null;

                $cms = Doctrine::getTable('ModerationComponent')
                    ->findOneByComponentInstanceId($instance_id);

                if ($_POST['s_upload_moderation'] == 'enable') {
                    if (!$cms) {
                        $cms = new ModerationComponent();
                        $cms['component_instance_id'] = $instance_id;
                    }
                    $cms['instance_id'] = $_POST['s_moderation_instance'];
                    $cms->save();

                } else {
                    if ($cms['id']) {
                        $cms->delete();
                    }
                }

                print("Your settings were successfully updated.");
                $crs->save();
                break;

            case "appearance":
                $cuss = Doctrine::getTable('ComponentRecorderStyle')
                ->findOneByInstanceId($instance_id);
                $width = $_POST['component_width'];
                $height = $_POST['component_height'];
                if ($width > 12000)
                    $width = 12000;

                if ($height > 10000)
                    $height = 10000;

                $cuss['width'] = $width;
                $cuss['height'] = $height;
                $cuss->save();
                print($width . ":" . $height . ";");
                break;

            case "transcode":
                $deletion = Doctrine_Query::create()
                                     ->delete('TranscodeComponentVideo')
                                     ->from('TranscodeComponentVideo tcv')
                                     ->where('tcv.component_id = ?',$instance_id)
                                     ->execute();

                foreach(explode(",", $_POST['transcode_profiles']) as $tvid) {
                    $id = Doctrine::getTable('TranscodeVideo')
                        ->findOneById($tvid);

                    $tcv = new TranscodeComponentVideo();
                    $tcv['component_id'] = $instance_id;
                    $tcv['video_id'] = $id['id'];
                    $tcv->save();
                }
                break;

            case 'accesslist':

                $ace = $_POST['domain_access_enable'];

                $ci = Doctrine::getTable('ComponentInstance')
                    ->findOneById($instance_id);

                if ($ace == 'enable') {

                    $ci['restrict_domains'] = true;

                    $deletion = Doctrine_Query::create()
                                        ->delete('ComponentDomain')
                                        ->from('ComponentDomain cd')
                                        ->where('cd.component_id = ?', $instance_id)
                                        ->execute();

                    foreach (explode(',', $_POST['domain_access_list']) as $domain) {
                        if ($domain == '')
                            continue;

                        $cd = new ComponentDomain();
                        $cd['component_id'] = $instance_id;
                        $cd['domain'] = $domain;
                        $cd->save();
                    }
                } else {
                    $ci['restrict_domains'] = false;
                }

                $ci->save();
                print("Your settings have successfully been saved.");
                break;

            default:
                return false;
                break;
            }
        } else {
            return false;
        }
    }

    public function settingsAction() {

        $ident = new FlixnIdentification();
        $userid = $this->identity['id'];
        $this->_checkInstance();
        $instance_id = $this->_request->getParam('instance');
        $this->view->instanceId = $instance_id;

        $ci = new ComponentInstance();
        $ci = Doctrine::getTable('ComponentInstance')
            ->findOneById($instance_id);

        $this->view->componentId = $ci['component_id'];
        $this->view->instanceName = $ci['name'];

        $crs = Doctrine::getTable('ComponentRecorderSetting')
            ->findOneByInstanceId($instance_id);

        if ($crs == false) {
            $crs = new ComponentRecorderSetting();
            $crs['instance_id'] = $ci['id'];
            $crs['video'] = true;
            $crs['high_quality'] = true;
            $crs['time_limit'] = 300;
            //default 5 min
            $crs['high_quality'] = true;
            $crs['style_id'] = 1;
            $crs->save();
        }

        $time_limit_h = floor($crs->time_limit / 3600);
        $time_limit_m = floor(($crs->time_limit - ($time_limit_h * 3600))/60);
        $time_limit_s = floor($crs->time_limit - (($time_limit_m * 60) + ($time_limit_h * 3600)));
        $this->view->s_timeHours = $time_limit_h;
        $this->view->s_timeMinutes = $time_limit_m;
        $this->view->s_timeSeconds = $time_limit_s;

        if ($crs['video']) {
            $this->view->s_typeSelectVideo = 'checked="checked"';
            $this->view->s_typeSelectAudio = '';
        } else {
            $this->view->s_typeSelectVideo = '';
            $this->view->s_typeSelectAudio = 'checked="checked"';
        }

        if ($crs->high_quality) {
            $this->view->s_hqSelectEnable = 'checked="checked"';
            $this->view->s_hqSelectDisable = '';
        } else {
            $this->view->s_hqSelectEnable = '';
            $this->view->s_hqSelectDisable = 'checked="checked"';
        }

        $cms = Doctrine::getTable('ModerationComponent')
            ->findOneByComponentInstanceId($instance_id);

        if ($cms['component_instance_id'] == $instance_id) {
            $this->view->moderatedSelectEnable = 'checked="checked"';
            $this->view->selected = $cms['instance_id'];
        } else {
            $this->view->moderatedSelectEnable = '';
            $this->view->moderatedSelectDisable = 'checked="checked"';
        }

        $transcode = Doctrine::getTable('TranscodeVideo')
                        ->findByUserId($userid);

        $transcode_options = Doctrine_Query::create()
                                ->select('tv.name, tv.id')
                                ->from('TranscodeVideo tv')
                                ->where('tv.user_id = ?',$userid)
                                ->addWhere('tv.id NOT IN (SELECT tcv.video_id FROM TranscodeComponentVideo tcv WHERE tcv.component_id = '.$instance_id.')')
                                ->execute();

        $transcode_selected = Doctrine_Query::create()
                                ->select('id, name')
                                ->from('TranscodeVideo tv, TranscodeComponentVideo tcv')
                                ->where("tcv.component_id = $instance_id")
                                ->addWhere("tcv.video_id = tv.id")
                                ->execute();

        $this->view->transcodeAvailable = $transcode_options;
        $this->view->transcodeSelected = $transcode_selected;

        $css = Doctrine::getTable('ComponentRecorderStyle')
        ->findOneByInstanceId($instance_id);

        if (!$css) {
            $css = new ComponentRecorderStyle();
            $css['instance_id'] = $ci['id'];
            $css['width'] = 320;
            $css['height'] = 275;
            $css->save();
        }

        $this->view->componentWidth = $css['width'];
        $this->view->componentHeight = $css['height'];

        if ($ci['restrict_domains'] == true) {
            $domain_list = Doctrine_Query::create()
                            ->select('domain')
                            ->from('ComponentDomain')
                            ->where("component_id = $instance_id")
                            ->execute();

            $this->restrictDomainsDisabled = 'false';

            $this->view->restrictDomainsList = $domain_list;
            $this->view->restrictDomainsEnable = 'checked="checked"';
            $this->view->restrictDomainsDisable = '';
        } else {
            $this->restrictDomainsDisabled = 'true';

            $this->view->restrictDomainsList = array();
            $this->view->restrictDomainsEnable = '';
            $this->view->restrictDomainsDisable = 'checked="checked"';
        }
    }

    private function _checkInstance()
    {
        $instance_id = $this->_request->getParam('instance');
        if (!is_numeric($instance_id)) {
            $this->_redirect('/' . $this->_request->getParam('controller') . '/index');
        }

        $instances = Doctrine_Query::create()
        ->select('id,name')
        ->from('ComponentType ct, ComponentInstance ci')
        ->where("ct.name=:name AND ci.user_id=:user_id AND ci.type_id=ct.id",
        array(':name' => 'recorder',
        ':user_id' => $this->identity['id']))
        ->count();

        if (count($instances) > 0) {
            return true;
        } else {
            $this->_redirect('/' . $this->_request->getParam('controller') . '/index');
        }
    }

    private function _getModerationInstances()
    {
        $instances = Doctrine::getTable('ModerationInstance')
        ->findByUserId($this->identity['id']);
        $this->view->moderationInstances = $instances;
    }
}