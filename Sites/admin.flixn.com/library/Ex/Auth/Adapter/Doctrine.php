<?php
/**
 * Exhibition
 *
 * @category   Exhibition
 * @package    Ex_Auth
 * @subpackage Ex_Auth_Adapter
 * @copyright  Copyright (c) 2008 Evilcode Corporation (http://www.evilcode.net)
 * @license
 * @version    $Id$
 */


/**
 * @see Zend_Auth_Adapter_Interface
 */
require_once 'Zend/Auth/Adapter/Interface.php';

/**
 * @see Zend_Auth_Result
 */
require_once 'Zend/Auth/Result.php';


/**
 * @category   Exhibition
 * @package    Exhibition_Auth
 * @subpackage Exhibition_Auth_Adapter
 * @copyright  Copyright (c) 2008 Evilcode Corporation (http://www.evilcode.net)
 * @license
 */
class Ex_Auth_Adapter_Doctrine implements Zend_Auth_Adapter_Interface
{
    /**
     * __construct() - Sets configuration options
     *
     * @param  string  $modelName
     * @param  string  $identityColumn
     * @param  string  $credentialColumn
     * @param  string  $credentialTreatment
     * @return void
     */
    public function __construct($modelName = null, $identityColumn = null,
                                $credentialColumn = null, $credentialTreatment = null)
    {
        if ($modelName !== null) {
            $this->setModelName($modelName);
        }

        if ($identityColumn !== null) {
            $this->setIdentityColumn($identityColumn);
        }

        if ($credentialColumn !== null) {
            $this->setCredentialColumn($credentialColumn);
        }

        if ($credentialTreatment !== null) {
            $this->setCredentialTreatment($credentialTreatment);
        } else {
            $this->setCredentialTreatment('?');
        }
    }
    
    /**
     * $_modelName - Doctrine model
     *
     * @var string
     */
    protected $_modelName = null;

    /**
     * $_identityColumn - the column to use as the identity
     *
     * @var string
     */
    protected $_identityColumn = null;

    /**
     * $_credentialColumns - columns to be used as the credentials
     *
     * @var string
     */
    protected $_credentialColumn = null;

    /**
     * $_credentialTreatment - Treatment applied to the credential, such as MD5() or PASSWORD()
     *
     * @var string
     */
    protected $_credentialTreatment = null;
    
    /**
     * $_identity - Identity value
     *
     * @var string
     */
    protected $_identity = null;

    /**
     * $_credential - Credential values
     *
     * @var string
     */
    protected $_credential = null;

    /**
     * $_resultObj - Results of Doctrine authentication query
     *
     * @var array
     */
    protected $_resultObj = null;

    /**
     * setModelName() - set the doctrine model name to query against
     *
     * @param  string $modelName
     * @return Ex_Auth_Adapter_Doctrine Provides a fluent interface
     */
    public function setModelName($modelName)
    {
        $this->_modelName = $modelName;
        return $this;
    }

    /**
     * setIdentityColumn() - set the column name to be used as the identity column
     *
     * @param  string $identityColumn
     * @return Ex_Auth_Adapter_Doctrine Provides a fluent interface
     */
    public function setIdentityColumn($identityColumn)
    {
        $this->_identityColumn = $identityColumn;
        return $this;
    }

    /**
     * setCredentialColumn() - set the column name to be used as the credential column
     *
     * @param  string $credentialColumn
     * @return Ex_Auth_Adapter_Doctrine Provides a fluent interface
     */
    public function setCredentialColumn($credentialColumn)
    {
        $this->_credentialColumn = $credentialColumn;
        return $this;
    }
    
    /**
     * setCredentialTreatment() - allows the developer to pass a parameterized string that is
     * used to transform or treat the input credential data
     *
     * In many cases, passwords and other sensitive data are encrypted, hashed, encoded,
     * obscured, or otherwise treated through some function or algorithm. By specifying a
     * parameterized treatment string with this method, a developer may apply arbitrary SQL
     * upon input credential data.
     *
     * Examples:
     *
     *  'PASSWORD(?)'
     *  'MD5(?)'
     *
     * @param  string $treatment
     * @return Ex_Auth_Adapter_Doctrine Provides a fluent interface
     */
    public function setCredentialTreatment($treatment)
    {
        $this->_credentialTreatment = $treatment;
        return $this;
    }
    
    /**
     * setIdentity() - set the value to be used as the identity
     *
     * @param  string $value
     * @return Ex_Auth_Adapter_Doctrine Provides a fluent interface
     */
    public function setIdentity($value)
    {
        $this->_identity = $value;
        return $this;
    }

    /**
     * setCredential() - set the credential value to be used, optionally can specify a treatment
     * to be used, should be supplied in parameterized form, such as 'MD5(?)' or 'PASSWORD(?)'
     *
     * @param  string $credential
     * @return Ex_Auth_Adapter_Doctrine Provides a fluent interface
     */
    public function setCredential($credential)
    {
        $this->_credential = $credential;
        return $this;
    }

    /**
     * getResultRowObject() - Returns the Doctrine result object
     *
     * @return Doctrine_Record|boolean
     */
    public function getResultObject()
    {
        if ($this->_resultObj === null)
            return false;

        $ret = $this->_resultObj->toArray();
        return $ret[0];
    }
        
    /**
     * authenticate() - defined by Zend_Auth_Adapter_Interface.
     *
     * @throws Zend_Auth_Adapter_Exception If XXX
     * @return Zend_Auth_Result
     */
    public function authenticate()
    {
        $this->_authenticateSetup();
    
        $user = Doctrine_Query::create()
                ->from($this->_modelName)
                ->where("$this->_identityColumn=?", $this->_identity)
                ->addWhere("$this->_credentialColumn=$this->_credentialTreatment",
                           $this->_credential)
                //->setHydrationMode(Doctrine::HYDRATE_ARRAY)
                ->execute();
        
        if (count($user) > 0) {
            $this->_resultObj = $user;
            $code = Zend_Auth_Result::SUCCESS;
            $message = 'Authentication successful.';
        } else {
            $code = Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID;
            $message = 'Supplied credential is invalid.';
        }
        
        return new Zend_Auth_Result($code, $this->_identity, array($message));
    }
    
    /**
     * _authenticateSetup() - This method abstracts the steps involved with making sure
     * that this adapter was indeed setup properly with all required peices of information.
     *
     * @throws Zend_Auth_Adapter_Exception - in the event that setup was not done properly
     * @return true
     */
    protected function _authenticateSetup()
    {
        $exception = null;

        if ($this->_modelName === null) {
            $exception = 'A Doctrine model must be supplied for the Ex_Auth_Adapter_Doctrine authentication adapter.';
        } elseif ($this->_identityColumn === null) {
            $exception = 'An identity column must be supplied for the Ex_Auth_Adapter_Doctrine authentication adapter.';
        } elseif ($this->_credentialColumn === null) {
            $exception = 'A credential column must be supplied for the Ex_Auth_Adapter_Doctrine authentication adapter.';
        } elseif ($this->_identity === null) {
            $exception = 'A value for the identity was not provided prior to authentication with Ex_Auth_Adapter_Doctrine.';
        } elseif ($this->_credential === null) {
            $exception = 'A credential value was not provided prior to authentication with Ex_Auth_Adapter_Doctrine.';
        }

        if ($exception !== null) {
            /**
             * @see Zend_Auth_Adapter_Exception
             */
            require_once 'Zend/Auth/Adapter/Exception.php';
            throw new Zend_Auth_Adapter_Exception($exception);
        }
        
        $this->_authenticateResultInfo = array(
            'code'     => Zend_Auth_Result::FAILURE,
            'identity' => $this->_identity,
            'messages' => array()
            );
            
        return true;
    }
}
