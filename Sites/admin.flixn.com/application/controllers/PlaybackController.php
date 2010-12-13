<?php

require_once 'Zend/Controller/Action.php';

class PlaybackController extends Zend_Controller_Action {

    private $identity;

    public function init() {
        $auth = Zend_Auth::getInstance();
        $this->identity = $auth->getIdentity();

        if (!$this->identity)
            $this->_redirect('/auth/login');

        $this->view->BaseUrl = $this->_request->getBaseUrl();
        $this->view->Controller = $this->_request->getParam('controller');
        $this->view->Path = $this->_request->getPathInfo();

        $this->_getModerationInstances();
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
                $ci = new ComponentInstance();
                $ci = Doctrine::getTable('ComponentInstance')
                        ->findOneById($instance_id);
                $this->view->instanceName = $ci['name'];

                if ($ci['name'] != $_POST['s_name']) {
                    $ci['name'] = $_POST['s_name'];
                    $ci->save();
                }

                $cps = Doctrine::getTable('ComponentPlayerSetting')
                            ->findOneByInstanceId($instance_id);

                if (!$cps)
                    $cps = new ComponentPlayerSetting();

				if (isset($_POST['s_embed']))
	                if ($_POST['s_embed'] == "enable")
	                    $cps['embed'] = true;
	                else
	                    $cps['embed'] = false;

				if (isset($_POST['s_share']))
	                if ($_POST['s_share'] == "enable")
	                    $cps['share'] = true;
	                else
	                    $cps['share'] = false;

				if (isset($_POST['s_email']))
					if ($_POST['s_email'] == "enable")
					   $cps['email'] = true;
					else
					    $cps['email'] = false;

				if (isset($_POST['s_sms']))
	                if ($_POST['s_sms'] == "enable")
	                    $cps['sms'] = true;
	                else
	                    $cps['sms'] = false;

				if (isset($_POST['s_info']))
	                if ($_POST['s_info'] == "enable")
	                    $cps['info'] = true;
	                else
	                    $cps['info'] = false;

				if (isset($_POST['s_full']))
	                if ($_POST['s_full'] == "enable")
	                    $cps['fullscreen'] = true;
	                else
	                    $cps['fullscreen'] = false;

				if (isset($_POST['s_pop']))
	                if ($_POST['s_pop'] == "enable")
	                    $cps['popout'] = true;
	                else
	                    $cps['popout'] = false;

				if (isset($_POST['s_light']))
	                if ($_POST['s_light'] == "enable")
	                    $cps['lighting'] = true;
	                else
	                    $cps['lighting'] = false;

 				if (isset($_POST['s_auto']))
					if ($_POST['s_auto'] == "enable")
						$cps['autoplay'] = true;
					else
						$cps['autoplay'] = false;

                $cps['instance_id'] = $instance_id;

                $cps->save();

                print("Your settings were successfully updated.");
                break;

            case "appearance":
                $crs = Doctrine::getTable('ComponentPlayerStyle')
                ->findOneByInstanceId($instance_id);
                $crs['width'] = $_POST['a_width'];
                $crs['height'] = $_POST['a_height'] + 15;
                $crs['aspect_id'] = $_POST['a_aspect'];
                $crs->save();
                print("Your settings were successfully updated.");
                return true;
                break;


            case "transcode":
                $deletion = Doctrine_Query::create()
                                    ->delete('TranscodePriority')
                                    ->from('TranscodePriority tp')
                                    ->where('tp.component_id = ?',$instance_id)
                                    ->execute();

                    $i = 0;
                    foreach(explode(",", $_POST['transcode_priority']) as $id)
                        {
                            $tp = new TranscodePriority();
                            $tp['component_id'] = $instance_id;
                            $tp['transcode_id'] = $id;
                            $tp['priority'] = $i;
                            $tp->save();
                            $i++;
                            unset($tp);
                        }
                print("Your settings were successfully updated.");
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

                break;

            default:
                return false;
                break;

            }
        } else {
            return false;
        }
    }

    public function indexAction() {
        $instance_id = $this->_request->getParam('instance');
        $redirect = $this->_request->getParam('instance');
        $action = $this->_request->getParam('action');
        if(isset($instance_id) && is_numeric($instance_id) && isset($redirect) && $action = "index")
        {
            $this->_redirect('/playback/settings/instance/' . $instance_id);
        }
    }

    public function settingsAction() {
        $ident = new FlixnIdentification();
        $userid = $this->identity['id'];
        $this->_checkInstance();
        $instance_id = $this->_request->getParam('instance');
        $this->view->instance_id = $instance_id;
        $ci = new ComponentInstance();
        $ci = Doctrine::getTable('ComponentInstance')
                    ->findOneById($instance_id);
        $this->view->componentId = $ci['component_id'];
        $this->view->instanceName = $ci['name'];
        $cps = Doctrine::getTable('ComponentPlayerSetting')
                    ->findOneByInstanceId($instance_id);

        if ($cps == false)
        {
            $cps = new ComponentPlayerSetting();
            $cps['instance_id'] = $ci['id'];
            $cps->save();
            unset($cps);
            $this->view->componentId = $ci['component_id'];
            $this->view->instanceName = $ci['name'];
            $cps = Doctrine::getTable('ComponentPlayerSetting')
                    ->findOneByInstanceId($instance_id);

            //take advantage of defaults
        }



        if($cps['embed'] == true)
        {
            $this->view->embedSelectEnable = 'checked="checked"';
            $this->view->embedSelectDisable = '';
        }
        else
        {
            $this->view->embedSelectEnable = '';
            $this->view->embedSelectDisable = 'checked="checked"';
        }


        if($cps['share'] == true)
        {
            $this->view->shareSelectEnable = 'checked="checked"';
            $this->view->shareSelectDisable = '';
        }
        else
        {
            $this->view->shareSelectEnable = '';
            $this->view->shareSelectDisable = 'checked="checked"';
        }
        if($cps['email'] == true)
        {
            $this->view->emailSelectEnable = 'checked="checked"';
            $this->view->emailSelectDisable = '';
        }
        else
        {
            $this->view->emailSelectEnable = '';
            $this->view->emailSelectDisable = 'checked="checked"';
        }

       if( $cps['sms'] == true)
        {
            $this->view->smsSelectEnable = 'checked="checked"';
            $this->view->smsSelectDisable = '';
        }
        else
        {
            $this->view->smsSelectEnable = '';
            $this->view->smsSelectDisable = 'checked="checked"';
        }

        if($cps['info'] == true)
        {
            $this->view->infoSelectEnable = 'checked="checked"';
            $this->view->infoSelectDisable = '';
        }
        else
        {
            $this->view->infoSelectEnable = '';
            $this->view->infoSelectDisable = 'checked="checked"';
        }

        if($cps['fullscreen'] == true)
        {
            $this->view->fullSelectEnable = 'checked="checked"';
            $this->view->fullSelectDisable = '';
        }
        else
        {
            $this->view->fullSelectEnable = '';
            $this->view->fullSelectDisable ='checked="checked"';
        }

        if($cps['popout'] == true)
        {
            $this->view->popSelectEnable = 'checked="checked"';
            $this->view->popSelectDisable = '';
        }
        else
        {
            $this->view->popSelectEnable = '';
            $this->view->popSelectDisable = 'checked="checked"';
        }

        if($cps['lighting'] == true)
        {
            $this->view->lightSelectEnable = 'checked="checked"';
            $this->view->lightSelectDisable = '';
        }
        else
        {
            $this->view->lightSelectEnable = '';
            $this->view->lightSelectDisable = 'checked="checked"';
        }

        if($cps['autoplay'] == true)
        {
            $this->view->autoSelectEnable = 'checked="checked"';
            $this->view->autoSelectDisable = '';
        }
        else
        {
            $this->view->autoSelectEnable = '';
            $this->view->autoSelectDisable = 'checked="checked"';
        }

        $domainlock = Doctrine::getTable('ComponentDomainList')
                            ->findByUserId($userid);

        $this->view->domainInstances = $domainlock;

        if($cps['domainlock'])
            $this->view->domainLockSelected = 'selected="selected"';

        $transcode = Doctrine_Query::create()
                        ->select('id, name')
                        ->from('TranscodeVideo tv, TranscodePriority tp')
                        ->where('tp.transcode_id = tv.id')
                        ->addWhere('tv.user_id = ?', $userid)
                        ->addWhere('tp.component_id = ?', $instance_id)
                        ->orderby('tp.priority')
                        ->execute();

        $trns_tv = Doctrine_Query::create()
                    ->select('id')
                    ->from('TranscodeVideo')
                    ->where('user_id = ?', $userid)
                    ->orderby('bitrate DESC')
                    ->execute();


if ($transcode->count() == 0)
{
    $priority = 0;
            foreach ($trns_tv as $otv) {
                $tsetup = new TranscodePriority();
                $tsetup['priority'] = $priority;
                $tsetup['component_id'] = $instance_id;
                $tsetup['transcode_id'] = $otv->id;
                $tsetup->save();
                $priority++;
            }
	    
    $transcode = Doctrine_Query::create()
		    ->select('id, name')
		    ->from('TranscodeVideo tv, TranscodePriority tp')
		    ->where('tp.transcode_id = tv.id')
		    ->addWhere('tv.user_id = ?', $userid)
		    ->addWhere('tp.component_id = ?', $instance_id)
		    ->orderby('tp.priority')
		    ->execute();
}

/* THIS CHUNK OF CODE BREAKS SHIT. IT DOES NOT ACCOOUNT FOR WHEN YOU ADD A NEW PROFILE.*/
/*
        if ($transcode->count() < $trns_tv->count()) {
	    
	    $transcode_delete = Doctrine_Query::create()
                        ->delete()
                        ->from('TranscodeVideo tv, TranscodePriority tp')
                        ->where('tp.transcode_id = tv.id')
                        ->addWhere('tv.user_id = ?', $userid)
                        ->addWhere('tp.component_id = ?', $instance_id)
                        ->orderby('tp.priority')
                        ->execute();
	    /*
	   // $missing_profiles = array();
	    foreach($transcode_bak as $otv) {
		if ($transcode)
	    }
	    
            $priority = 0;
            foreach ($trns_tv as $otv) {
                $tsetup = new TranscodePriority();
                $tsetup['priority'] = $priority;
                $tsetup['component_id'] = $instance_id;
                $tsetup['transcode_id'] = $otv->id;
                //$tsetup->save();
                $priority++;
            }

            $transcode = Doctrine_Query::create()
                            ->select('id, name')
                            ->from('TranscodeVideo tv, TranscodePriority tp')
                            ->where('tp.transcode_id = tv.id')
                            ->addWhere('tv.user_id = ?', $userid)
                            ->addWhere('tp.component_id = ?', $instance_id)
                            ->orderby('tp.priority')
                            ->execute();
        }
*/
        $this->view->transcodeInstances = $transcode;

        $cp_style = Doctrine::getTable('ComponentPlayerStyle')
                                    ->findOneByInstanceId($instance_id);


        /*
        $this->view->styleSelected = $cp_style['style_id'];
        $styles = Doctrine_Query::create()
        ->from('ComponentRecorderStyle')
        ->where('user_id = ? OR user_id is null', $this->identity['id'])
        ->execute();
        $this->view->styleInstances = $styles;
        */

        $aspects = Doctrine::getTable('ComponentPlayerAspectRatio')
                                ->findAll();

        if (!$cp_style)
        {
            $default_aspect = Doctrine::getTable('ComponentPlayerAspectRatio')
                                    ->findOneByName("4x3");

            $cp_style = new ComponentPlayerStyle;
            $cp_style['aspect_id'] = $default_aspect['id'];
            $cp_style['width'] = 400;
            $co = ($default_aspect['aspect_w'] / $default_aspect['aspect_h']);
            $cp_style['height'] = (int)(ceil(($cp_style['width'] / $co) + 15));
            $cp_style['instance_id'] = $instance_id;
            $cp_style->save();
        }

        $b = array();
        foreach ($aspects as $a)
        {
            $checked = '';
            if($a['id'] == $cp_style['aspect_id'])
                $checked = "checked='checked'";
            else
                $checked = '';

            $b[] = array('id' => $a['id'], 'width' => $a['aspect_w'],
                    'height' => $a['aspect_h'], 'name' => $a['name'],
                    'checked' => $checked);

            //Doctrine provides a strict class for each model
            //that prevents adding properties to the result set
        }


        $this->view->aspects = $b;
        $this->view->a_width = $cp_style['width'];
        $this->view->a_height = $cp_style['height'] - 15;


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

    public function createAction() {
        $this->_helper->layout->disableLayout();
        $this->view->typeName = "Player";

        $ident = new FlixnIdentification();
        $userid = $this->identity['id'];

        $formData = $this->_request->getPost();

        if ($this->_request->isPost()) {

            $exists = Doctrine_Query::create()
                ->select('ci.id')
                ->from('ComponentInstance ci, ComponentType ct')
                ->where('ct.name = \'player\'')
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
                    ->findOneByName('player');

            //naming scheme difference to be reconciled

            $ci = new ComponentInstance();
            $ci['user_id'] = $this->identity['id'];
            $ci['type_id'] = $type['id'];
            $ci['component_id'] = $ident->UUIDGenerate(1);
            $ci['component_key'] = $ident->UUIDKeyGenerate();
            $ci['name'] = $formData['name'];
            $ci->save();

            $cpset = new ComponentPlayerSetting();

            $cpset['instance_id'] = $ci['id'];

            $cpset->save();

            $cpstyle = new ComponentPlayerStyle();
            $cpstyle['instance_id'] = $ci['id'];
            $cpstyle['width'] = 400;
            $cpstyle['height'] = 300;
            $cpstyle['aspect_id'] = 1;
            $cpstyle->save();

                print("1:" . $ci['id']);

            }

        }
    }


    private function _checkInstance() {
        $instance_id = $this->_request->getParam('instance');
        if (!is_numeric($instance_id))
            $this->_redirect('/' . $this->_request->getParam('controller') . '/index');

        $instances = Doctrine_Query::create()
                     ->select('id,name')
                     ->from('ComponentType ct, ComponentInstance ci')
                     ->where("ct.name=:name AND ci.user_id=:user_id AND ci.type_id=ct.id",
                             array(':name' => 'player',
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