<?php

class ExServicesClass extends ExServicesGrokclass {

    public $patterns = array(
        'var' => '/(\@var) ([\w_]+)(\[\])*[\ ]?(.*)/',
        'param' => '/(\@param) ([\w_]+)(\[\])* (\$[\w_]*),*([.]{3})*[\ ]?(.*)/',
        'return' => '/(\@return) ([\w_]+)(\[\])*[\ ]?(.*)/');

    public $ignoreMethods = array('__construct', '__destruct', '__get', '__set',
                                  '__call', '__sleep', '__wakeup');
    public $methods = array();
    public $parameters = array();
    public $returns = array();
    
    private $currentMethod = NULL;
    
    public function __construct($class) {
        parent::__construct($class);
    }
    
    public function addMethod($name) {
        $this->methods[] = $name;
        $this->parameters[$name] = array();
        $this->currentMethod = $name;
    }
    
    public function addParameter($name, $type, $array, $has_default, $default, $comment) {
        if ($this->currentMethod === NULL) {
            print "ExServicesClass: currentMethod(1) === NULL\n";
            throw new ExServicesException();
        }
        
        $this->parameters[$this->currentMethod][$name] = array('type' => $type,
                                                               'array' => $array,
                                                               'has_default' => $has_default,
                                                               'default' => $default,
                                                               'comment' => $comment);
    }
    
    public function addReturn($type, $array, $comment) {
        if ($this->currentMethod === NULL) {
            print "ExServicesClass: currentMethod(2) === NULL\n";
            throw new ExServicesException();
        }
        
        $this->returns[$this->currentMethod] = array('type' => $type,
                                                     'array' => $array,
                                                     'comment' => $comment);
    }
    
    public function newClass($name, $comment) {
        return true;
    }
    
    public function newMethod($name, $comment, $public, $parameters) {
        if (in_array($name, $this->ignoreMethods))
            return false;
        if (!$public)
            return false;
        
        $this->addMethod($name);
        
        $c_lines = explode("\n", $comment);
        array_shift($c_lines);
        array_pop($c_lines);

        foreach ($c_lines as $line) {
            if (preg_match($this->patterns['param'], $line, $param_match)) {
                $param_name = ltrim(trim($param_match[4]), '$');

                if (!array_key_exists($param_name, $parameters)) {
                    print "ExServicesClass: Array key does not exist: $param_name ... XXX: this means there is a parse problem w/ the class we pulled in, phpdoc+actual def. mismatch\n";
                    print_r($parameters);
                    throw new ExServicesException();
                }
                
                $arr = ($param_match[3] == '[]') ? true : false;
                $this->newMethodParameter($param_name, trim($param_match[2]), $arr,
                                          $parameters[$param_name]['default_value_available'],
                                          (isset($parameters[$param_name]['default_value']))
                                              ? $parameters[$param_name]['default_value']
                                              : NULL,
                                          trim($param_match[6]));
            }
            else if (preg_match($this->patterns['return'], $line, $return_match)) {
                $return_type = $return_match[2];
                if ($return_type != NULL) {
                    $arr = (trim($return_match[3]) == '[]') ? true : false;
                    $this->newMethodReturn(trim($return_type), $arr, trim($return_match[4]));
                }
            }
        }        
        
        return true;
    }
    
    public function newMethodParameter($name, $type, $array, $has_default, $default, $comment) {
        $this->addParameter($name, $type, $array, $has_default, $default, $comment);
    }
    
    public function newMethodReturn($type, $array, $comment) {
        $this->addReturn($type, $array, $comment);
    }
}
