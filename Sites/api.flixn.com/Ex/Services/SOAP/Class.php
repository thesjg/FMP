<?php

class ExServicesSOAPClass extends ExServicesClass {
    
    private $WSDL;
    private $currentPort;
    private $currentBinding;
        
    public function __construct($class) {
        parent::__construct($class);
    }

    public function newClass($name, $comment) {
        if (parent::newClass($name, $comment) == false)
            return false;
     
        $this->WSDL = new ExServicesSOAPDefinition($name, 'XXX');
        
        $this->currentPort = $name . 'Port';
        $this->WSDL->NewPortType($this->currentPort);
        
        $this->CurrentBinding = $name . 'Binding';
        $this->WSDL->NewBinding($this->currentBinding, 'tns:' . $name . 'Port');
        $this->WSDL->AddSoapBinding($this->currentBinding, 'rpc');

        $this->WSDL->AddService($name . 'Service', 'tns:' . $name . 'Port',
                                'tns:' . $this->currentBinding, 'XXX');
    }

    public function newMethod($name, $comment, $public, $parameters) {
        if (parent::newMethod($name, $comment, $public, $parameters) == false)
            return false;
        
        $this->addMethod($name);
            
        $c_lines = explode("\n", $comment);
        array_shift($c_lines);
        array_pop($c_lines);

          /* XXX: Hardcoded URL */
        $io_arr = array('use' => 'encoded',
                        'encodingStyle' => 'http://schemas.xmlsoap.org/soap/encoding/');
        $this->WSDL->AddBindingOperation($this->currentBinding, $name, $io_arr, $io_arr);
        $this->WSDL->AddSoapOperation($this->currentPort, 'XXX/soap/' . $this->currentClassName .
                                      '/' . $name);
        
        $request_name = $name . 'Request';
        $this->WSDL->NewMessage($request_name);
        $part_count = 0;
        
        foreach ($c_lines as $line) {
            if (preg_match($this->patterns['param'], $line, $param_match)) {
                $part_name = ltrim($param_match[4], '$');
                $part_type = $this->WSDL->GetType($param_match[2]);

                /* XXX: BUILD COMPLEX TYPE
                if ($parameters[$part_name]['optional'])
                */

                $this->WSDL->AddMessagePart($request_name, $part_name, $part_type);
                $part_count++;
            }
            else if (preg_match($this->patterns['return'], $line, $return_match)) {
                $return_type = $this->WSDL->GetType($return_match[2]);
                if ($return_type != NULL) {
                    $response_name = $name . 'Response';
                    $this->WSDL->NewMessage($response_name);
                    $this->WSDL->AddMessagePart($response_name, $name . 'Return', $return_type);
                    $part_count = 0;
                }
            }
        }

          /* No message parts, pull it */
        if ($part_count == 0)
            unset($this->WSDL->Messages[$request_name]);
        
        $this->WSDL->AddPortOperation($this->currentPort, $name,
                                      ($part_count) ? 'tns:' . $name . 'Request' : NULL,
                                      ($return_type) ? 'tns:' . $name . 'Response' : NULL);
    }

    public function Render() {
        $this->WSDL->Build();
        return $this->WSDL->Render();
    }
}