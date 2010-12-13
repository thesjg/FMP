<?php
/**
 * Exhibition
 *
 * @category    Exhibition
 * @package     Services
 * 
 * @author      Samuel J. Greear
 * @copyright   Copyright (c) 2005-2006 Samuel J. Greear
 * @copyright   Copyright (c) 2008 Evilcode Corporation
 * @version     $Id $
 */

class ExServicesGrokClass {

    public $currentClassName;
    public $maxDepth = 1;

    private $class;
    private $curDepth = 0;
    
    public function __construct($class) {
    
        $this->class = new ReflectionClass($class);
        $this->currentClassName = $class;
        
        $comment = $this->class->getDocComment();
        
//        $properties = $this->Class->getProperties();
//        var_export($properties);

        $this->newClass($this->currentClassName, $comment);
        
        foreach ($this->class->getMethods() as $method) {
            if (!$method->isInternal()) {
                $declaring_class = $method->getDeclaringClass();
                if ($declaring_class->name != $this->currentClassName) {
                    $this->curDepth++;
                    if ($this->maxDepth != 0 && $this->curDepth >= $this->maxDepth) {
                        break;
                    } else {
                        $this->currentClassName = $declaring_class->name;
                        $this->newClass($this->currentClassName, NULL);
                    }
                }

                $parameters = $method->getParameters();
                $parameter_arr = array();

                foreach ($parameters as $parameter) {
                    $parameter_arr[$parameter->getName()] =
                        array('optional' => $parameter->isOptional(),
                              'default_value_available' => $parameter->isDefaultValueAvailable());

                    if ($parameter_arr[$parameter->getName()]['default_value_available'])
                        $parameter_arr[$parameter->getName()]['default_value'] =
                            $parameter->getDefaultValue();
                }
                                
                $name = $method->getName();
                $comment = $method->getDocComment();
                $public = $method->isPublic();
                $this->newMethod($name, $comment, $public, $parameter_arr);
            }
        }
    }

    public function newClass($name, $comment) {
        print 'NewClass, Name: ' . $name . "\n";
        print "Comment: \n" . $comment . "\n\n";
    }

    public function newMethod($name, $comment, $public, $parameters) {
        print 'NewMethod, Name: ' . $name . ', ' .
              'Public: ' . (($public) ? 'True' : 'False') . "\n";
        print 'Parameters: ';
        foreach ($parameters as $key => $value)
            print $key . ',';
        print "\nComment: \n" . $comment . "\n\n";
    }
}