    <?php
    
    require_once 'Zend/Controller/Action.php';
    
    class ModerationController extends Zend_Controller_Action {
    
        private $identity;
    
        public function init() {
            $auth = Zend_Auth::getInstance();
            $this->identity = $auth->getIdentity();
            
            if (!$this->identity)
                $this->_redirect('/auth/login');
    
            $this->view->BaseUrl = $this->_request->getBaseUrl();
            $this->view->Controller = $this->_request->getParam('controller');
            $this->view->Path = $this->_request->getPathInfo();
        }
        
        public function indexAction()
        {
            
        }
        
        public function viewAction()
        {
            $this->_checkInstance();
            $ident = new FlixnIdentification();
            $userid = $this->identity['id'];
            $instance_id = $this->_request->getParam('instance');
            
            $this->view->instance_id = $this->_request->getParam('instance');
            $this->view->viewPath = "/moderation/";
            
            $page_interval = 12; 
            $page = $this->_request->getParam('page');
            
            //$_SESSION['filters'][$instance_id] = array($filter1);
            
            /*
             VALID FIELDS:
             iname = Instance Name
             url = URL
             key = Any variety of metadata - on-hold
             date = range of creation
             state = duh?
            */
            $filters = $_SESSION['filters'];
            $filter_id = $filters[$instance_id];
            $this->view->filters = $filter_id;
            
            //$media
            
            $select_clause = "m.media_id, state_id";
            $from_clause = "Medium m, ModerationMedium mo, MediumMetadatumCreation mmc, ModerationState ms, ComponentInstance ci, MediumMetadatum mm";
            
            $select_clause_alt = "ms.name";
            $from_clause_alt = "ModerationState ms, Medium m, ModerationMedium mo, MediumMetadatumCreation mmc, ComponentInstance ci, MediumMetadatum mm";
            
            
            $media = Doctrine_Query::create()
                    ->select($select_clause)
                    ->from($from_clause)
                    //->orderby("state_id")
                    ->where('m.user_id = '. $userid);

            if(count($_SESSION['filters'][$instance_id]) != 0)
            {
                foreach($_SESSION['filters'][$instance_id] as $filter)
                {
                    switch ($filter['mode'])
                    {
                        case "is":
                            $op = "=";
                            break;
                        
                        case "regex":
                            $op = "~*";
                            break;
                        
                        case "like":
                            $op = "~~*";
                            $filter['value'] = "%" . $filter['value'] . "%";
                            break;
                        
                        default:
                            $op = "=";
                        break;
                    }
                    
                    switch ($filter['field'])
                    {
                        case "iname":
                            $media->addWhere("ci.name $op '".$filter['value']."'")->addWhere("ci.id = mmc.component_id");
                            break;
                        
                        case "url":
                            $media->addWhere("mmc.url $op '".$filter['key']."'");
                            break;
                        
                        case "cdate":
                            //$media->addWhere("date_trunc('day',mcm.creation_timestamp) = date_trunc('day',TIMESTAMP ".$filter['keys']."')");
                            $media->addWhere("mmc.url $op '".$filter['key']."'");
                        break;
                        
                        case "state":
                            $media->addWhere("ms.name $op '".$filter['key']."'");
                        break;
                        
                        default:
                        break;
                    }

                }
            } else {
                $media->addWhere('m.id = mo.media_id')
                        ->addWhere('mo.state_id = ms.id')
                        ->addWhere('ms.name IN (SELECT ms2.name FROM ModerationState ms2 WHERE ms2.name = \'PENDING\' OR ms2.name = \'FLAGGED\')')
                        ->addWhere('ci.id IN (SELECT mc.component_instance_id FROM ModerationComponent mc where mc.instance_id = '. $instance_id.')');
           }
        
            $media_state = $media;
            
            $count = $media->distinct()->count();
            
            //XXX XXX XXX XXX XXX XXX XXX XXX XXX XXX XXX XXX XXX XXX
            //XXX copy for receiving state name -> doctrine sucks XXX
            //XXX XXX XXX XXX XXX XXX XXX XXX XXX XXX XXX XXX XXX XXX
    
           
                $media->limit($page_interval);
                $media_state->limit($page_interval);
                
            if(is_numeric($page) && $page != 0)
            {
                $media->offset($page * $page_interval);
                $media_state->offset($page * $page_interval);
            }
            
            $media = $media->distinct()
                        ->execute();
                        
            $media_state = $media_state->select($select_clause_alt)
                        ->from($from_clause_alt)
                        ->distinct()
                        ->execute();
                
            //array merge would be lovely, but no, doctrine sucks
            $media_array = array();
            
            foreach ($media as $key => $m)
            {
                $media_array[$key]['media_id'] = $m['media_id'];
                $media_array[$key]['state'] = $media_state[$key]['name'];
            }
            
            if($count > $page_interval)
            {
                if(!is_numeric($page))
                {
                    $page = 0;
                }
                $this->view->page = $page;
                if ($count >= ($page_interval ++))
                {
                $this->view->more_pages = true;
                }
            }
            
            $this->view->media = $media_array;
        }
        
        public function actionAction()
        {
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender();
            if($this->_request->isPost())
            {
                
                $media_id = $this->_request->getParam('media');
                
                //get the states
                $reject_state = Doctrine::getTable('ModerationState')
                                ->findOneByName("DENIED");
                                
                $approve_state = Doctrine::getTable('ModerationState')
                                ->findOneByName("APPROVED");
                                
                $media_id = Doctrine::getTable('Medium')
                                ->findOneByMediaId($media_id);
                                
                $media = Doctrine::getTable('ModerationMedium')
                                ->findOneByMediaId($media_id['id']);
                                
                if($media == false)
                {
                    $media = new ModerationMedium();
                    $media['media_id'] = $media_id['id'];
                }
    
                //digging those variable names
                
                $post = $_POST;
                switch ($post['action'])
                {
                    case "reject":
                        $media['state_id'] = $reject_state['id'];
                    break;
                
                    case "approve":
                        $media['state_id'] = $approve_state['id'];
                    break;
                
                    default:
                    break;
                }
                
                $media->save();
                print(true);
                
            }
            
        }
        
        public function clearAction()
        {
            $instance_id = $this->_request->getParam('instance');
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender();
            $filters = $_SESSION['filters'];
            $filters[$instance_id] = null;
            $_SESSION['filters'] = $filters;
            $this->_redirect('/moderation/view/instance/' . $instance_id);
        }
        
        public function removeAction()
        {
            $instance_id = $this->_request->getParam('instance');
            $filter_id = $this->_request->getParam('filter');
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender();
            $filters = $_SESSION['filters'];
            $filters[$instance_id][$filter_id]['key'] = null;
            
            $re_filters = array();
            
            foreach($filters[$instance_id] as $f)
            {
                if($f['key'] != null)
                {
                    $re_filters[] = $f;
                }
            }
                
            $filters[$instance_id] = $re_filters;    
                
            $_SESSION['filters'] = $filters;
            $this->_redirect('/moderation/view/instance/' . $instance_id);
        }
    
    
        public function createAction()
        {
            $this->_helper->layout->disableLayout();
            $this->view->typeName = "Moderation";
            
            $ident = new FlixnIdentification();
            $userid = $this->identity['id'];
            $formData = $this->_request->getPost();
            
            if ($this->_request->isPost()) {
                
                $exists = Doctrine_Query::create()
                    ->select('mi.id')
                    ->from('ModerationInstance mi')
                    ->where('mi.name = \''. $formData['name'].'\'')
                    ->addWhere('mi.user_id= '. $userid)
                    ->distinct()
                    ->execute();
                

                $this->_helper->viewRenderer->setNoRender();
                    
                if (is_numeric($exists[0]['id'])) {
                    print("0:" . $exists[0]['id']);
                }
                else
                {
                    
                    $mi = new ModerationInstance();
                    
                    $mi['user_id'] = $this->identity['id'];
                    $mi['name'] = $formData['name'];
                    
                    if($formData['defer'] == "enable")
                        $mi['deferred'] = true;
                    else
                        $mi['deferred'] = false;
                        
                    $mi->save();
                    
                    print("1:" . $mi['id']);
                }
            }
        }
        
        public function addAction()
        {
            if ($this->_request->isPost() && isset($_POST['new_value']) && isset($_POST['new_field']) && isset($_POST['new_type']))
            {
                $this->_helper->layout->disableLayout();
                $this->_helper->viewRenderer->setNoRender();
                $this->_checkInstance();
                $instance_id = $this->_request->getParam('instance');
                $filters = $_SESSION['filters'];
                $id_filter = $filters[$instance_id];
                $id_filter[] = array('key' => $_POST['new_value'], 'field' => $_POST['new_field'], 'mode' => $_POST['new_type']);
                $filters[$instance_id] = $id_filter;
                $_SESSION['filters'] = $filters;
                $this->_redirect('/moderation/view/instance/' . $instance_id);
            }
        }

        private function _checkInstance() {
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