<?php
/**
 * Exhibition
 *
 * @category    Exhibition
 * @package     Services
 *
 * @author      Samuel J. Greear
 * @copyright   Copyright (c) 2008 Evilcode Corporation
 * @version     $Id $
 */

class ExServicesDispatch {

    private $_serviceDefinition;
    private $_target;
    private $_parameters;

    public function __construct($serviceDefinition) {

        $this->_serviceDefinition = $serviceDefinition;
        $sD = $this->_serviceDefinition;

        //try
        /* Instantiate the class that is the target of the incoming request */
        if ($sD->requestService == ExServices::SOAP)
            $this->_target = new ExServicesSOAPClass($sD->requestClass);
        else if ($sD->requestService == ExServices::REST)
            $this->_target = new ExServicesRESTClass($sD->requestClass);

        if (!in_array($sD->requestMethod, $this->_target->methods))
            throw new ExServicesException(ExServicesErrors::UNKNOWN_METHOD);

        $this->_parameters = array();
        foreach ($this->_target->parameters[$sD->requestMethod] as $key => $value) {

            $name = strtolower($key);
            if (!array_key_exists($name, $sD->requestVariables))
                if ($value['has_default'] === false)
                    throw new ExServicesException(ExServicesErrors::INVALID_ARGUMENT);
                else
                    $this->_parameters[$name] = $value['default'];
            else
                $this->_parameters[$name] = $sD->requestVariables[$name];
        }
    }

    public function dispatch() {

        $sD = $this->_serviceDefinition;

        $a = new $sD->requestClass();
        $method = $sD->requestMethod;

        $ret_value = call_user_func_array(array($a, $sD->requestMethod),
                                          $this->_parameters);

        $ret_type = $this->_target->returns[$sD->requestMethod];
        $serializer = new ExServicesSerializer($ret_value, $ret_type['type'],
                                               $ret_type['array'],
                                               $ret_type['comment']);

        return $serializer->serialize();
    }
}