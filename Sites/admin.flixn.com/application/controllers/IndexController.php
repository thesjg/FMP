<?php
require_once 'Zend/Controller/Action.php';

class IndexController extends Zend_Controller_Action
{
    
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
    
    public function indexAction()
    {
    }
    
    private function _getModerationInstances() {
    
    $instances = Doctrine::getTable('ModerationInstance')
                ->findByUserId($this->identity['id']);

    $this->view->moderationInstances = $instances;
    }
}