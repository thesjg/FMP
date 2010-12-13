<?php
require_once 'Zend/Controller/Action.php';

class AuthController extends Zend_Controller_Action {

    public function init() {
        $this->view->Controller = $this->_request->getParam('controller');
        $this->_helper->layout->disableLayout();
    }


    public function loginAction()
    {
        
        if ($this->_request->isPost()) {
            $authAdapter = new Ex_Auth_Adapter_Doctrine();
            $authAdapter->setModelName('User')
                        ->setIdentityColumn('username')
                        ->setCredentialColumn('password');
                    
            $formData = $this->_request->getPost();
            if ($formData['username'] == '') {
                $this->view->status = 'You must provide a username';
                return;
            }
            $authAdapter->setIdentity($formData['username'])
                        ->setCredential($formData['password']);
        
            $auth = Zend_Auth::getInstance();
        
            $result = $auth->authenticate($authAdapter);

            switch ($result->getCode()) {
                case Zend_Auth_Result::SUCCESS:
                    $auth->getStorage()->write($authAdapter->getResultObject());
                    $this->_redirect('/');
                    break;
                default:
                    $this->view->status = 'Invalid username/password combination.';
                    break;
            }
        }
    }

    public function forgotAction()
    {
        $form = $_POST;
        if ($this->_request->isPost() && isset($form['username']) && isset($form['email'])) {
        
		if (filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        
        		$user = Doctrine_Query::create()
                	   	 ->select('u.password')
                   		 ->from('User u, UserDatum ud')
                    		 ->where('u.username = ?', $form['username'])
                    		 ->addWhere('ud.email = ?', $form['email'])
                    		 ->execute();
		} else {
			$user = false;
		}
        
	        if(!$user)
        	    $this->view->status = "Invalid username/email combination.";
        	else
        	{
            		$message = "Flixn FMP Password Reminder \n Username: ". $form['username'] ."\n Password: " . $user[0]['password'] . "\n\n";
            		$headers = "From: reset@flixn.com" . "\r\n" .
                        		"X-Mailer: PHP/" . phpversion();

   	                mail($form['email'], "Flixn - FMP Password", $message, $headers);

	                $this->view->status = "Your password has been sent.";
                }
          }
    }
    
    
    public function logoutAction()
    {
        $auth = Zend_Auth::getInstance(); 
        $auth->clearIdentity();
        $this->_redirect('/auth/login');
    }
}
