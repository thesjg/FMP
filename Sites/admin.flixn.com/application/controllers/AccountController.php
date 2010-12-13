<?php
require_once 'Zend/Controller/Action.php';

class AccountController extends Zend_Controller_Action
{
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
    }
    
    public function ajaxAction()
    {
        $ident = new FlixnIdentification();
        $userid = $this->identity['id'];
        
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if ($this->_request->isXmlHttpRequest())
        {
        // use superglobal request, <> _POST
        $ud = Doctrine::getTable('UserDatum')
                    ->findOneByUserId($userid);
        if(!$ud){$ud=new UserDatum();}
        $ud['name'] = $_POST['p_name'];
        $ud['company'] = $_POST['p_company'];
        $ud['title'] = $_POST['p_title'];
        $ud['address'] = $_POST['p_address'];
        $ud['email'] = $_POST['p_email'];
        $ud['phone'] = $_POST['p_phone'];
        $ud['user_id'] = $userid;
        $ud->save();
        print("Your settings were successfully updated.");
        }
    }

    
    
    public function indexAction()
    {
        //settings
        $ident = new FlixnIdentification();
        $userid = $this->identity['id'];
        $user_data = Doctrine::getTable('UserDatum')
                        ->findOneByUserId($userid);
                        
        if(!$user_data)
        {
            $user_data= new UserDatum();
            $user_data->save();
        }
              
        $this->view->pName = $user_data['name'];
        $this->view->pCompany = $user_data['company'];
        $this->view->pTitle = $user_data['title'];
        $this->view->pAddress = $user_data['address'];
        $this->view->pEmail = $user_data['email'];
        $this->view->pPhone = $user_data['phone'];
    }
    
    public function logoutAction()
    {
        Zend_Auth::getInstance()->clearIdentity();
        $this->_redirect('/auth/login');
    }
}