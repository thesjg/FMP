<?php

class ExServicesSOAPDefinition extends ExXMLElement {

    public $Messages;
    public $Types;
    public $PortTypes;
    public $Bindings;
    public $Services;
    
    public function __construct($name, $uri, $documentation=NULL) {
        parent::__construct('definitions', NULL, 'wsdl');

          // XXX: URI defaults to NULL, pull from defaults like elsewhere
        
        $this->AddDocumentation($this, $documentation);
                
        $this->SetAttribute('name', $name);
        $this->SetAttribute('targetNamespace', $uri);
        $this->SetAttribute('xmlns:tns', $uri);

        global $_CONF;
        $this->SetAttribute('xmlns:xsd', $_CONF['namespaces']['xsd']);
        $this->SetAttribute('xmlns:soap-enc', $_CONF['namespaces']['soap-enc']);
       
        $this->Messages = array();
        $this->Types = NULL;
        $this->PortTypes = array();
        $this->Bindings = array();
        $this->Services = array();
    }

    public function NewMessage($name, $documentation=NULL) {
        $message = $this->ForkChild('message');
        $this->AddDocumentation($message, $documentation);
        $message->SetAttribute('name', $name);
    
        $this->Messages[$name] = $message;
    }

    public function AddMessagePart($m_name, $name, $type) {
        $part = $this->Messages[$m_name]->ForkChild('part');
        $part->SetAttribute('name', $name);
        $part->SetAttribute('type', $type);
        $this->Messages[$m_name]->AddChild($part);
    }

    public function NewPortType($name, $documentation=NULL) {
        $porttype = $this->ForkChild('portType');
        $this->AddDocumentation($porttype, $documentation);
        $porttype->SetAttribute('name', $name);
        
        $this->PortTypes[$name] = $porttype;
    }

    public function AddPortOperation($pt_name, $name, $input=NULL, $output=NULL, $fault=NULL,
                                     $documentation=NULL) {
          
          // XXX: Better input checking (strings, exception throwing)
          
        $operation = $this->PortTypes[$pt_name]->ForkChild('operation');
        $this->AddDocumentation($operation, $documentation);
        $operation->SetAttribute('name', $name);
        
        if ($input != NULL) {
            $inode = $operation->ForkChild('input');
            $inode->SetAttribute('message', $input);
            $operation->AddChild($inode);
        }
        
        if ($output != NULL) {
            $onode = $operation->ForkChild('output');
            $onode->SetAttribute('message', $output);
            $operation->AddChild($onode);
        }

        if ($fault != NULL) {
            $fnode = $operation->ForkChild('fault');
            $fnode->SetAttribute('message', $fault);
            $operation->AddChild($fnode);
        }

        $this->PortTypes[$pt_name]->AddChild($operation);
    }

    public function AddSoapOperation($pt_name, $soap_action, $documentation=NULL) {
        $soperation = new ExXMLElement('operation', NULL, 'soap');
        $this->AddDocumentation($soperation, $documentation);
        $soperation->SetAttribute('soapAction', $soap_action);

        $this->PortTypes[$pt_name]->AddChild($soperation);
    }
        
    public function NewBinding($name, $pt_name, $documentation=NULL) {
        $binding = $this->ForkChild('binding');
        $this->AddDocumentation($binding, $documentation);
        $binding->SetAttribute('name', $name);
        $binding->SetAttribute('type', $pt_name);
        
        $this->Bindings[$name] = $binding;
    }

    /**
     *
     * @param array $input (keys: 'use', 'namespace', 'encodingStyle')
     * @param array $output (keys: 'use', 'namespace', 'encodingStyle')
     * @param array $fault (keys: 'name', 'use', 'namespace', 'encodingStyle')
     * @param string $documentation
     */
    public function AddBindingOperation($b_name, $name, $input=NULL, $output=NULL, $fault=NULL,
                                        $documentation=NULL) {
        
        $operation = $this->Bindings[$b_name]->ForkChild('operation');
        $this->AddDocumentation($operation, $documentation);
        $operation->SetAttribute('name', $name);

        $nodes = array();
        if ($input != NULL)
            $nodes['input'] = $input;
        if ($output != NULL)
            $nodes['output'] = $output;
        if ($fault != NULL)
            $nodes['fault'] = $fault;

        foreach ($nodes as $node_name => $node_attrs) {
            $node = $operation->ForkChild($node_name);
            
            $sbody = new ExXMLElement('body', NULL, 'soap');
            foreach ($node_attrs as $key => $value)
                $sbody->SetAttribute($key, $value);
            $node->AddChild($sbody);
            $operation->AddChild($node);
        }
        
        $this->Bindings[$b_name]->AddChild($operation);
    }

      /* XXX: Hardcoded URL */
    public function AddSoapBinding($b_name, $style='document',
                                   $transport='http://schemas.xmlsoap.org/soap/http',
                                   $documentation=NULL) {
        
        $sbinding = new ExXMLElement('binding', NULL, 'soap');
        $this->AddDocumentation($sbinding, $documentation);
        
        $sbinding->SetAttribute('style', $style);
        $sbinding->SetAttribute('transport', $transport);

        $this->Bindings[$b_name]->AddChild($sbinding);
    }

    public function AddService($name, $port_name, $binding, $location, $documentation=NULL) {
        $service = $this->ForkChild('service');
        $this->AddDocumentation($service, $documentation);
        $service->SetAttribute('name', $name);
        
        $port = $service->ForkChild('port');
        $port->SetAttribute('name', $port_name);
        $port->SetAttribute('binding', $binding);
        
        $address = new ExXMLElement('address', NULL, 'soap');
        $address->SetAttribute('location', $location);
        
        $port->AddChild($address);
        $service->AddChild($port);

        $this->Services[$name] = $service;
    }

    public function AddTypes($types) {
        if ($this->Types = NULL)
            $this->Types = $this->ForkChild('types');

        // XXX: Implement me, XML Schema types
    }
    
    private function AddDocumentation(&$node, $documentation) {
        if ($documentation == NULL)
            return;

        $docnode = $this->ForkChild('documentation', $documentation);
        $node->AddChild($docnode);
    }

    public function GetType($type) {
        switch ($type) {
              /* Built-in Schema Types */
            case 'str':            
            case 'string':
                return 'xsd:string';
                break;
            case 'int':
            case 'integer':
                return 'xsd:integer';
                break;
            case 'float':
                return 'xsd:float';
                break;
            case 'bool':
            case 'boolean':
                return 'xsd:boolean';
                break;
            case 'object':
                return 'xsd:struct';
                break;
            
              /* Complex Types */
            case 'array':
                return 'soap-enc:Array';
                break;
            
              /* Ignore */
            case 'void':
                return NULL;
            
            default:
                return 'xsd:anyType';
         }
     }
    
    public function Build() {
    
        foreach ($this->Messages as $message)
            $this->AddChild($message);

        if ($this->Types != NULL)
            $this->AddChild($this->Types);

        foreach ($this->PortTypes as $porttype)
            $this->AddChild($porttype);

        foreach ($this->Bindings as $binding)
            $this->AddChild($binding);

        foreach ($this->Services as $service)
            $this->AddChild($service);
    }
}