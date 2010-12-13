<?php
require_once 'Zend/Controller/Action.php';

class StatisticsController extends Zend_Controller_Action
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
        $stats = new StatisticsWindow($this->identity['id']);
        $front = $stats->getFrontStats();
        $this->view->stats = $front;
        $this->view->graph = $stats->retrieveOverview();
        
    }
    
    public function testAction()
    {
    $this->_helper->layout->disableLayout();
    $this->_helper->viewRenderer->setNoRender();
    $stats = new StatisticsWindow($this->identity['id']);
    
    $data = $stats->getStatistics("July 4 2008", "August 19 2008", 10, null);
    echo "<html><head><title>LOL NO ERROR</title></head><body>LOLLERSKATES <br />";
    echo "<pre>";
    print_r($data);
    echo "</pre>";
    echo  "</body></html>";
    }
    
    
    public function topAction()
    {
        $mode = $this->_request->getParam('mode');
        
        $userid = $this->identity['id'];
        
        $stats = new StatisticsWindow($this->identity['id']);
        
        $this->view->data = $stats->retrieveOverview();
        
    }
    
    public function detailAction()
    {
        
        
        $stats = new StatisticsWindow($this->identity['id']);
        
        $instance_id = $this->_request->getParam('instance');
        $media_id = $this->_request->getParam('media');
        $start = $this->_request->getParam('start');
        $end = $this->_request->getParam('end');

        $start_date = explode("-", $start);
        $end_date = explode("-", $end);
        
        $start = date("F j Y", mktime(0,0,0,$start_date[0],$start_date[1],$start_date[2]));
        $end = date("F j Y", mktime(0,0,0,$end_date[0],$end_date[1],$end_date[2]));
        
        $data = $stats->getStatistics($start, $end, $instance_id, null);
        
        //$data = $stats->getStatistics("August 4 2008", "August 19 2008", null, null);
        
        $this->view->graphData = $data;

        $this->view->startDate = $start;
        $this->view->endDate = $end;
        $this->view->instance_id = $instance_id;

    }
    
    private function _getModerationInstances() {
    
    $instances = Doctrine::getTable('ModerationInstance')
                ->findByUserId($this->identity['id']);

    $this->view->moderationInstances = $instances;
    }
}