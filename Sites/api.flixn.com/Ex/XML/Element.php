<?php

class ExXMLElement extends DOMDocument {

    public $Namespace = NULL;
    public $NamespaceURI = NULL;

    private $ValueNode = NULL;

    public function __construct($name, $value=NULL, $namespace=NULL,
                                $namespace_uri=NULL) {
        parent::__construct();

        /* XXX: ??? Make output pretty if we're debugging */
        $this->formatOutput = true;

        $_CONF = array();
        $_CONF['namespaces'] = array();
        $_CONF['namespaces']['exhibition'] = 'http://evilcode.net/exhibition/namespaces/';
        $_CONF['namespaces']['wsdl'] = 'http://schemas.xmlsoap.org/wsdl/';
        $_CONF['namespaces']['soap'] = 'http://schemas.xmlsoap.org/wsdl/soap/';
        $_CONF['namespaces']['xsd'] = 'http://www.w3.org/2001/XMLSchema';
        $_CONF['namespaces']['soap-enc'] = 'http://schemas.xmlsoap.org/soap/encoding/';
        $_CONF['namespaces']['atom'] = 'http://www.w3.org/2005/Atom';

        if ($namespace == NULL) {
            $node = $this->createElement($name);
        } else {
            $this->Namespace = $namespace;
            $name = $namespace . ':' . $name;

            if ($namespace_uri == NULL) {
                if (array_key_exists($namespace, $_CONF['namespaces']))
                    $this->NamespaceURI = $_CONF['namespaces'][$namespace];
                else
                    $this->NamespaceURI = $_CONF['namespaces']['exhibition'] .
                                          $namespace;
            } else {
                $this->NamespaceURI = $namespace_uri;
            }
                    
            $node = $this->createElementNS($this->NamespaceURI, $name);
        }

        $this->appendChild($node);
                        
        if ($value != NULL)
            $this->SetValue($value);
    }

    public function ForkChild($name, $value=NULL) {
        return new ExXMLElement($name, $value, $this->Namespace);
    }

    public function SetValue($value) {
        $old = $this->ValueNode;

        $this->ValueNode = $this->createTextNode((string)$value);
        if ($this->ValueNode == false)
            return false;

        if ($old != NULL)
            $this->removeChild($this->firstChild);

        $this->firstChild->appendChild($this->ValueNode);
    }

    public function SetAttribute($name, $value) {
        $this->firstChild->setAttribute($name, $value);
    }

    public function AddElement($name, $value) {
        if ($this->Namespace == NULL)
            $this->AddChild(new ExXMLElement($name, $value));
        else
            $this->AddChild(new ExXMLElement($name, $value, $this->Namespace));
    }

    public function AddChild($child) {
        $node = $this->importNode($child->firstChild, true);
        $this->firstChild->appendChild($node);
    }

    public function Render() {
        return $this->saveXML();
    }
}