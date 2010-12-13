<?php

require_once 'Zend/Controller/Action.php';

class UploadedController extends Zend_Controller_Action {

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


    public function ajaxAction() {
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

                $cus = Doctrine::getTable('ComponentUploaderSetting')
                ->findOneByInstanceId($instance_id);

                if ($ci['name'] != $_POST['s_name'])
                {
                    $ci['name'] = $_POST['s_name'];
                    $ci->save();
                }

                if ($_POST['s_type'] == 'single')
                    $cus['single'] = true;
                else
                    $cus['single'] = false;

                $file_lim = $_POST['s_file_size'];
                $size_lim = $_POST['s_size'];

                if ($file_lim > 1500)
                    $file_lim = 1500;

                if ($size_lim > 20000)
                    $size_lim = 20000;

                $cus['file_limit'] = (int)round($file_lim * 1000 * 1000);
                $cus['size_limit'] = (int)round($size_lim  * 1000 * 1000);


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
                $cus->save();
                print($size_lim . ":" . $file_lim . ";");

                break;

            case "appearance":
                $cuss = Doctrine::getTable('ComponentUploaderStyle')
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

                break;

            default:
                return false;
                break;
            }
        } else {
            return false;
        }
    }

    public function indexAction()
    {
         $instance_id = $this->_request->getParam('instance');
        $redirect = $this->_request->getParam('instance');
        $action = $this->_request->getParam('action');
        if(isset($instance_id) && is_numeric($instance_id) && isset($redirect) && $action = "index")
        {
            $this->_redirect('/uploaded/settings/instance/' . $instance_id);
        }
    }

    public function createAction() {
        $this->_helper->layout->disableLayout();
        $this->view->typeName = "Uploader";

        $ident = new FlixnIdentification();
        $userid = $this->identity['id'];

        $formData = $this->_request->getPost();

        if ($this->_request->isPost()) {

            $exists = Doctrine_Query::create()
                ->select('ci.id')
                ->from('ComponentInstance ci, ComponentType ct')
                ->where('ct.name = \'uploader\'')
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
                        ->findOneByName('uploader');

                $ci = new ComponentInstance();
                $ci['user_id'] = $this->identity['id'];
                $ci['type_id'] = $type['id'];
                $ci['component_id'] = $ident->UUIDGenerate(1);
                $ci['component_key'] = $ident->UUIDKeyGenerate();
                $ci['name'] = $formData['name'];
                $ci->save();

                $cuset = new ComponentUploaderSetting();
                $cuset['instance_id'] = $ci['id'];
                $cuset['single'] = true;
                $cuset['size_limit'] = 100000000;
                $cuset['file_limit'] = 100000000;
                $cuset->save();

                $custyle = new ComponentUploaderStyle();
                $custyle['instance_id'] = $ci['id'];
                $custyle['width'] = 400;
                $custyle['height'] = 50;
                $custyle->save();

                print("1:" . $ci['id']);

            }

        }
    }

    public function settingsAction() {

        $ident = new FlixnIdentification();
        $userid = $this->identity['id'];
        $this->_checkInstance();

        $instance_id = $this->_request->getParam('instance');
        $this->view->instanceId = $instance_id;
        $ci = Doctrine::getTable('ComponentInstance')
                        ->findOneById($instance_id);
        $this->view->componentId = $ci['component_id'];
        $this->view->instanceName = $ci['name'];

        $cus = Doctrine::getTable('ComponentUploaderSetting')
                        ->findOneByInstanceId($instance_id);


        if (!$cus) {
            $cus = new ComponentUploaderSetting();
            $cus['instance_id'] = $ci['id'];
            $cus['single'] = true;
            $cus['size_limit'] = 100000000;
            $cus['file_limit'] = 100000000;
            $cus->save();
        }

        $cms = Doctrine::getTable('ModerationComponent')
                ->findOneByComponentInstanceId($instance_id);

        if ($cms['component_instance_id'] == $instance_id) {
            $this->view->moderatedSelectEnable = 'checked="checked"';
            $this->view->moderatedSelectDisable = '';
            $this->view->selected = $cms['instance_id'];
        } else {
            $this->view->moderatedSelectEnable = '';
            $this->view->moderatedSelectDisable = 'checked="checked"';
            }

        $this->view->sizeLimit = $cus['size_limit'] / 1000 / 1000;
        $this->view->fileLimit = $cus['file_limit'] / 1000 / 1000;

        switch ($cus['single']) {
            case true:
                $this->view->s_typeSelectMultiple = '';
                $this->view->s_typeSelectSingle = 'checked="checked"';
                break;

            case false:
                $this->view->s_typeSelectMultiple = 'checked="checked"';
                $this->view->s_typeSelectSingle = '';
                break;

                default:
                $this->view->s_typeSelectMultiple = '';
                $this->view->s_typeSelectSingle = 'checked="checked"';
                break;
        }

        //$this->view->selected2 = $crs['style_id'];
        //$uid = $this->identity['id'];
        /*$styles = Doctrine_Query::create()
        ->from('ComponentRecorderStyle')
        ->where('user_id = ? OR user_id is null', $this->identity['id'])
        ->execute();
        $this->view->styleInstances = $styles;
        */

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


        $css = Doctrine::getTable('ComponentUploaderStyle')
        ->findOneByInstanceId($instance_id);

        if (!$css) {
            $css = new ComponentUploaderStyle();
            $css['instance_id'] = $ci['id'];
            $css['width'] = 400;
            $css['height'] = 50;
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

    public function statisticsAction()
    {
        $this->_checkInstance();
    }

    private function _checkInstance()
    {
        $instance_id = $this->_request->getParam('instance');
        if (!is_numeric($instance_id)) {
            $this->_redirect('/' . $this->_request->getParam('controller') . '/index');
        }

        $instances = Doctrine_Query::create()
        ->from('ComponentType ct, ComponentInstance ci')
        ->where("ct.name=:name AND ci.user_id=:user_id AND ci.type_id=ct.id",
        array(':name' => 'uploader',
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