<?php

require_once 'Zend/Controller/Action.php';

class IngestionController extends Zend_Controller_Action {
    
    private $identity;
    
    public function init() {
        $auth = Zend_Auth::getInstance();
        $this->identity = $auth->getIdentity();
        $this->userid = $this->identity['id'];
        if (!$this->identity)
            $this->_redirect('/auth/login');

        $this->view->BaseUrl = $this->_request->getBaseUrl();
        $this->view->Controller = $this->_request->getParam('controller');
        $this->view->Path = $this->_request->getPathInfo();
        
    }
    
    public function indexAction()
        {
            
            $this->instanceInitializer();
            
        }

    private function instanceInitializer()
        {
                        
        $ident = new FlixnIdentification();
        $userid = $this->identity['id'];
        
        $state = Doctrine::getTable('ComponentState')
                            ->findOneByState("INGEST");

        $type_u = Doctrine::getTable('ComponentType')
                            ->findOneByName('uploader');
                            
                            
        $type_r = Doctrine::getTable('ComponentType')
                            ->findOneByName('recorder');
                        
        $cr = Doctrine_Query::create()
                    ->select('ci.*')
                    ->from("ComponentInstance ci, ComponentState cs")
                    ->where("ci.user_id = ?",$userid)
                    ->addWhere("cs.state = 'INGEST'")
                    ->addWhere("cs.id = ci.state_id")
                    ->addWhere("ci.type_id = ?", $type_r['id'])
                    ->execute();
                     
        if(count($cr) == 0)
            {
                
                $cr[0] = new ComponentInstance();
                $type = Doctrine::getTable('ComponentType')->findOneByName('recorder');
                            
                $cr[0]['type_id'] = $type_r['id'];
                $cr[0]['user_id'] = $userid;
                $cr[0]['restrict_domains'] = false;
                $cr[0]['component_id'] = $ident->UUIDGenerate(1);
                $cr[0]['component_key'] = $ident->UUIDKeyGenerate();
                $cr[0]['name'] = "Ingestion Recorder";
                $cr[0]['state_id'] = $state['id'];
                $cr[0]->save();
                unset($cr[0]);
                
                //refresh for assigned ID
                $cr = Doctrine_Query::create()
                    ->from("ComponentInstance ci, ComponentState cs")
                    ->where("ci.user_id = ?",$userid)
                    ->addWhere("cs.state = 'INGEST'")
                    ->addWhere("cs.id = ci.state_id")
                    ->execute();
            }
            
        $cu = Doctrine_Query::create()
                    ->select("ci.*")
                    ->from("ComponentInstance ci, ComponentState cs")
                    ->where("ci.user_id = ?",$userid)
                    ->addWhere("cs.state = 'INGEST'")
                    ->addWhere("cs.id = ci.state_id")
                    ->addWhere("ci.type_id = ?", $type_u['id'])
                    ->execute();            

        if (count($cu) == 0)
            {
                $cu[0] = new ComponentInstance();
                            
                $cu[0]['type_id'] = $type_u['id'];
                $cu[0]['user_id'] = $userid;
                $cu[0]['restrict_domains'] = false;
                $cu[0]['component_id'] = $ident->UUIDGenerate(1);
                $cu[0]['component_key'] = $ident->UUIDKeyGenerate();
                $cu[0]['name']= "Ingestion Uploader";
                $cu[0]['state_id'] = $state['id'];
                $cu[0]->save();
                unset($cu[0]);
                
                //refresh for assigned ID
                $cu = Doctrine_Query::create()
                    ->from("ComponentInstance ci, ComponentState cs")
                    ->where("ci.user_id = ?",$userid)
                    ->addWhere("cs.state = 'INGEST'")
                    ->addWhere("cs.id = ci.state_id")
                    ->addWhere("ct.name = 'uploader'")
                    ->addWhere("ci.type_id = ?", $type_u['id'])
                    ->execute();
            }
        
  
        $crstyle = Doctrine::getTable('ComponentRecorderStyle')
                    ->findOneByInstanceId($cr[0]['id']);
   
        if ($crstyle == false) {
            $crstyle = new ComponentRecorderStyle();
            $crstyle['instance_id'] = $cr[0]['id'];
            $crstyle['width'] = 400;
            $crstyle['height'] = 300;
            $crstyle->save();
        }
             
        $crs = Doctrine::getTable('ComponentRecorderSetting')
            ->findOneByInstanceId($cr[0]['id']);

        if ($crs == false) {
            $crs = new ComponentRecorderSetting();
            $crs['instance_id'] = $cr[0]['id'];
            $crs['video'] = true;
            $crs['high_quality'] = true;
            $crs['time_limit'] = 60 * 60 * 12;
            //default 12 hours
            $crs['high_quality'] = true;
            $crs['style_id'] = $crstyle['id'];
            $crs->save();
        }

    
        $custyle = Doctrine::getTable('ComponentUploaderStyle')
                    ->findOneByInstanceId($cu[0]['id']);
        
        if ($custyle == false) {
            $custyle = new ComponentUploaderStyle();
            $custyle['instance_id'] = $cu[0]['id'];
            $custyle['width'] = 400;
            $custyle['height'] = 300;
            $custyle->save();
        }      
        $cus = Doctrine::getTable('ComponentUploaderSetting')
                        ->findOneByInstanceId($cu['id']);
                        
        if ($cus == false) {
            $cus = new ComponentUploaderSetting();
            $cus['instance_id'] = $cu[0]['id'];
            $cus['single'] = false;
            $cus['size_limit'] = 1024 * 1024 * 1024 * 1.5; // 1.5 gb
            $cus['file_limit'] = 1024 * 1024 * 1024 * 32;
            $cus['style_id'] = $custyle['id'];
            $cus->save();

        }  
        $this->view->recorder_id = $cr[0]['component_id'];
        $this->view->uploader_id = $cu[0]['component_id'];
        $this->view->recorder = $cr[0]['id'];
        $this->view->uploader = $cu[0]['id'];
    }

}
    