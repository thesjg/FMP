<?php

/*

Copyright (C) 2004-2005 Samuel J. Greear. All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions
are met:
1. Redistributions of source code must retain the above copyright
   notice, this list of conditions and the following disclaimer.
2. Redistributions in binary form must reproduce the above copyright
   notice, this list of conditions and the following disclaimer in the
   documentation and/or other materials provided with the distribution.

THIS SOFTWARE IS PROVIDED BY AUTHOR AND CONTRIBUTORS ``AS IS'' AND
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
ARE DISCLAIMED.  IN NO EVENT SHALL AUTHOR OR CONTRIBUTORS BE LIABLE
FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS
OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF
SUCH DAMAGE.

$Id: form.php.class 138 2005-03-28 04:27:50Z sjg $

*/


/**
 * Form
 */
class Form extends XMLement {

    protected $Name;
    public $Parent;
    public $Error;
    public $Valid = false;
    public $ValidationError;
    public $ValidatedFunction = NULL;
    public $InvalidFunction = NULL;
   
    public $ValidationErrorMessage;

    public function __construct($name, $action, $method=NULL, $enctype=NULL,
                         $acceptcharset=NULL, $target=NULL) {
        parent::__construct('form', NULL, 'form');

        global $_CONF;
        $this->ValidationError = array();

        $this->AddElement('action', $action);

        if ($method != NULL)
            $this->AddElement('method', $method);
        else
            $this->AddElement('method', $_CONF['form']['method']);

        if ($enctype != NULL)
            $this->AddElement('enctype', $enctype);
        else
            $this->AddElement('enctype', $_CONF['form']['enctype']);

        if ($acceptcharset != NULL)
            $this->AddElement('accept-charset', $acceptcharset);
        else
            $this->AddElement('accept-charset',
                              $_CONF['form']['accept-charset']);

        if ($target != NULL)
            $this->AddElement('target', $target);

        $this->Name = $name;
        $hidden = new FormInput('hidden', '_FormName', $this->Name);
        $helement = new FormElement();
        $helement->AddChild($hidden->Build());
        $this->AddChild($helement);

        $this->ValidationErrorMessage = 'There was an error submitting the form.  Please correct the problem and try again.';
        $this->Error = new FormError($this->ValidationErrorMessage);
    }

    public function ValidateError($name, $err) {
        $this->Valid = false;
        $this->ValidationError[$name] = $err;
    }

    public function GetValidateErrors() {
        if (count($this->ValidationError)) {
            return $this->ValidationErrorMessage;
        }
    }
  
    public function SetError( $message ) {
        $this->Error = new FormError($message);
    } 

    public function SetEvent($event, $action) {
        $eventElement = new XMLement('event', NULL, 'form');
        $eventElement->AddElement($event, $action);

        $this->Group->AddChild($eventElement);
    }

    public function Validate() {
        global $_FORM;

        if ($_FORM['_FormName'] != $this->Name)
                return false;

        ex_debug("Validating form $this->Name", 2);
        $this->Valid = true;

          /* XXX */
        $rendered_xml = $this->Render();
        $xml2 = new DOMDocument();
        $xml2->loadXML($rendered_xml);
        $this->removeChild($this->firstChild);
        $xml = $this->importNode($xml2->firstChild, true);
        parent::appendChild($xml);

        foreach ($this->getElementsByTagNameNS($this->NamespaceURI,
                 'element') as $element) {

            $required = false;
            $validation = NULL;
            $validation_error = NULL;
            $maxlength = NULL;
            $name = '';
            $title = '';

              // See if this element is required
            $req = $element->getElementsByTagNameNS($this->NamespaceURI,
                                                   'required');
            if ($req->length > 0) {
                $req = $req->item(0);
                if ($req->textContent == 'true')
                    $required = true;
            }

              // Get validation regex if it exists
            $vld = $element->getElementsByTagNameNS($this->NamespaceURI,
                                                    'validation');
            if ($vld->length > 0) {
                $vld = $vld->item(0);
                $validation = $vld->textContent;
            }

              // Get validation error string
            $verr = $element->getElementsByTagNameNS($this->NamespaceURI,
                                                     'validation_error');
            if ($verr->length > 0) {
                $verr = $verr->item(0);
                $validation_error = $verr->textContent;
            }

              // Get maxlength if set
            $maxlen = $element->getElementsByTagNameNS($this->NamespaceURI,
                                                       'maxlength');
            if ($maxlen->length > 0) {
                $maxlen = $maxlen->item(0);
                $maxlength = $maxlen->textContent;
            }

            $curtype = 'input';
            $node = $element->getElementsByTagNameNS($this->NamespaceURI,
                                                     'input');
            if ($node instanceof DOMNodeList) {
                $node = $node->item(0);
            }

            if ($node == NULL) {
                $curtype = 'select';
                $node = $element->getElementsByTagNameNS($this->NamespaceURI,
                                                         'select');
                if ($node instanceof DOMNodeList)
                    $node = $node->item(0);
            }

            if ($node == NULL) {
                $curtype = 'textarea';
                $node = $element->getElementsByTagNameNS($this->NamespaceURI,
                                                         'textarea');
                if ($node instanceof DOMNodeList)
                    $node = $node->item(0);
            }

            if (!$node instanceof DOMElement)
                continue;

              // What is the name of this element?
            $iname = $node->getElementsByTagNameNS($this->NamespaceURI, 'name');
            $iname = $iname->item(0);
            $name = substr($iname->textContent, 5);

              // Get the title
            $ititle = $element->getElementsByTagNameNS($this->NamespaceURI, 'title');
            $ititle = $ititle->item(0);
            if ($ititle)
                $title = $ititle->textContent;
            else
                $title = '';

              // Preserve set form elements
              // XXX: 'select' type will require different handling
            if (isset($_FORM[$name])) {
                if ($curtype != 'select') {
                    $oldvalue
                        = $node->getElementsByTagNameNS($this->NamespaceURI,
                                                        'value');
                    if ($oldvalue->length > 0)
                        $node->removeChild($oldvalue->item(0));

                    $newvalue = $this->ForkChild('value', $_FORM[$name]);
                    $newnode = $this->importNode($newvalue->firstChild, true);
                    $node->appendChild($newnode);
/*
                    if ($curtype == 'input') {
                        $itype = $node->getElementsByTagNameNS($this->NamespaceURI, 'type');
                        $itype = $itype->item(0);
                        if ($itype && $itype->textContent == 'checkbox') {
                            $newvalue = $this->ForkChild('checked', 'true');
                            $newnode = $this->importNode($newvalue->firstChild, true);
                            $node->appendChild($newnode);
                        }
                    }
*/
                }
                if ($curtype == 'select') {
                }
            }

              // This is quite a ways along in the processing so that
              // set form elements are preserved
            if ($required == false && $validation == NULL)
                continue;

              // XXX: Functionalize addition of invalid child

              // Handle array's in input
            if ($name{strlen($name)-1} == ']') {
                list($key_name, $key_offset) = explode('[', $name);
                $key_offset = (int)substr($key_offset, 0, -1);
                $form_ptr = ($_FORM[$key_name][$key_offset]) ? $_FORM[$key_name][$key_offset] : '';
            } else {
                $form_ptr = ($_FORM[$name]) ? $_FORM[$name] : '';
            }

              // Actually do the validation
              // Check that required elements are filled in
            if ($required == true) {

                if ($form_ptr == '') {
                    $errstr = "Form field '" . $title . "' is required.";
                    $this->ValidateError($name, $errstr);

                    $errel = $this->ForkChild('invalid', $errstr);
                    $newnode = $this->importNode($errel->firstChild, true);
                    $element->appendChild($newnode);

                    continue;
                }
            }

              // XXX: Add support for optional validation function
              // Check input against validation regex
            if ($validation != NULL) {
                if (!eregi($validation, $form_ptr)) {
                    $errstr = "Form field '" . $title . "' contained invalid information.";

                    if ($validation_error != NULL)
                        $this->ValidateError($name, $validation_error);
                    else
                        $this->ValidateError($name, $errstr);

                    $errel = $this->ForkChild('invalid', $errstr);
                    $node = $this->importNode($errel->firstChild, true);
                    $element->appendChild($node);

                    continue;
                }
            }

              // Check maxlength
            if ($maxlength != NULL) {
                if (strlen($form_ptr) > $maxlength) {
                    $errstr = "Form field '" . $title . "' exceeds the maximum " .
                              "allowable length of " . $maxlength . " characters.";
                    $this->ValidateError($name, $errstr);

                    $errel = $this->ForkChild('invalid', $errstr);
                    $node = $this->importNode($errel->firstChild, true);
                    $element->appendChild($node);

                    continue;
                }
            }
        }

        
        if ($this->Valid == true)
            $this->Validated();
        else 
            $this->Invalid();

        $this->AddChild($this->Error);

        return true;
    }

    public function Validated() {
        global $_CONF;

        if ($this->ValidatedFunction != NULL)
            return call_user_func($this->ValidatedFunction);

        if ($_CONF['debug'] == true)
            ex_debug('Form::Validated method can be implemented in subclass');
    }

    public function Invalid() {
        global $_CONF;

        $this->SetError($this->GetValidateErrors());

        if ($this->InvalidFunction != NULL)
            return call_user_func($this->InvalidFunction);

        if ($_CONF['debug'] == true)
            ex_debug('Form::Invalid method can be implemented in subclass');
    }

    public function SetInvalid($field_name, $error_string, $type='input') {
        foreach ($this->getElementsByTagNameNS($this->NamespaceURI, 'element') as $element) {
            $name = $title = '';

            $node = $element->getElementsByTagNameNS($this->NamespaceURI, $type);
            if ($node instanceof DOMNodeList) {
                $node = $node->item(0);
            }

            if (!$node instanceof DOMElement)
                continue;

              // What is the name of this element?
            $iname = $node->getElementsByTagNameNS($this->NamespaceURI, 'name');
            $iname = $iname->item(0);
            $name = substr($iname->textContent, 5);

            if ($field_name != $name)
                continue;

              // Get the title
            $ititle = $element->getElementsByTagNameNS($this->NamespaceURI, 'title');
            $ititle = $ititle->item(0);
            if ($ititle)
                $title = $ititle->textContent;
            else
                $title = '';

            $this->ValidateError($name, $error_string);

            $errel = $this->ForkChild('invalid', $error_string);
            $node = $this->importNode($errel->firstChild, true);
            $element->appendChild($node);

            $this->SetError($this->ValidationErrorMessage);
            return;
        }
    }
}

class FormGroup extends XMLement {

    public function __construct($heading=NULL, $overview=NULL) {
        parent::__construct('group', NULL, 'form');

        if ($heading != NULL)
            $this->AddElement('heading', $heading);

        if ($overview != NULL)
            $this->AddElement('overview', $overview);
    }
}

class FormElement extends XMLement {

    public function __construct($title=NULL, $caption=NULL, $required=false,
                                $validation=NULL, $verr=NULL) {
        parent::__construct('element', NULL, 'form');

        if ($title != NULL)
            $this->AddElement('title', $title);

        if ($caption != NULL)
            $this->AddElement('caption', $caption);

        if ($required == true)
            $this->AddElement('required', 'true');

        if ($validation != NULL)
            $this->AddElement('validation', $validation);

        if ($verr != NULL)
            $this->AddElement('validation_error', $verr);
    }
}

class FormInput extends XMLement {

    public $Type;
    protected $Name;
    public $Value;
    public $Maxlength;

    public $Accept = NULL;
    public $Alt = NULL;
    public $Checked = false;
    public $Disabled = false;
    public $Readonly = false;
    public $Size = NULL;
    public $Src = NULL;

    public $Error = '';

    public function __construct($type, $name=NULL, $value=NULL, $maxlength=NULL) {
        parent::__construct('input', NULL, 'form');

        global $_CONF;

        $this->Type = $type;
        if ($name != NULL)
            $this->Name = $_CONF['form']['prefix'] . $name;
        else
            $this->Name = NULL;

        $this->Value = $value;
        $this->Maxlength = $maxlength;
    }

    public function Build() {
        if ($this->Type == 'checkbox' || $this->Type == 'radio')
            if ($this->Value == NULL) {
                $this->Error = "Value is required with types 'checkbox' " .
                               "and 'radio'";
                throw new Exception($this->Error);
            }

        $this->AddElement('type', $this->Type);

        if ($this->Name == NULL)
            if ($this->Type == 'button' || $this->Type == 'checkbox' ||
                $this->Type == 'file' || $this->Type == 'hidden' ||
                $this->Type == 'image' || $this->Type == 'password' ||
                $this->Type == 'text' || $this->Type == 'radio') {

                $this->Error = "Name is required for types 'button', " .
                               "'checkbox', 'file', 'hidden', 'image'" .
                               "'password', 'text' and 'radio'";
                throw new Exception($this->Error);
            }
        $this->AddElement('name', $this->Name);

        if ($this->Value != NULL)
            if ($this->Type != 'file') {
                $this->AddElement('value', $this->Value);
            } else {
                $this->Error = "Value may not be used with type 'file'";
                throw new Exception($this->Error);
            }

        if ($this->Accept != NULL)
            if ($this->Type == 'file') {
                $this->AddElement('accept', $this->Accept);
            } else {
                $this->Error = "Accept may only be used with type 'file'.";
                throw new Exception($this->Error);
            }

        if ($this->Alt != NULL)
            if ($this->Type == 'image') {
                $this->AddElement('alt', $this->Alt);
            } else {
                $this->Error = "Alt may only be used with type 'image'.";
                throw new Exception($this->Error);
            }

        if ($this->Checked == true)
            if ($this->Type == 'checkbox' || $this->Type == 'radio') {
                $this->AddElement('checked', 'true');
            } else {
                $this->Error = "Checked may only be used with types " .
                               "'checkbox' or 'radio'.";
                throw new Exception($this->Error);
            }

        if ($this->Disabled == true)
            if ($this->Type != 'hidden') {
                $this->AddElement('disabled', 'true');
            } else {
                $this->Error = "Disabled may not be used with type 'hidden'";
                throw new Exception($this->Error);
            }

        if ($this->Maxlength != NULL)
            if ($this->Type == 'text') {
                $this->AddElement('maxlength', $this->Maxlength);
            } else {
                $this->Error = "Maxlength may only be used with type 'text'";
                throw new Exception($this->Error);
            }

        if ($this->Readonly == true)
            if ($this->Type == 'text') {
                $this->AddElement('readonly', 'true');
            } else {
                $this->Error = "Readonly may only be used with type 'text'";
                throw new Exception($this->Error);
            }

        if ($this->Size != NULL)
            if ($this->Type != 'hidden') {
                $this->AddElement('size', $this->Size);
            } else {
                $this->Error = "Size may not be used with type 'hidden'";
                throw new Exception($this->Error);
            }

        if ($this->Src != NULL)
            if ($this->Type == 'image') {
                $this->AddElement('src', $this->Src);
            } else {
                $this->Error = "Src may only be used with type 'image'";
                throw new Exception($this->Error);
            }

        return $this;
    }
}

class FormSelect extends XMLement {

    public function __construct($name, $size=NULL, $multiple=false, $disabled=false) {
        parent::__construct('select', NULL, 'form');

        global $_CONF;

        $this->AddElement('name', $_CONF['form']['prefix'] . $name);

        if ($size != NULL)
            $this->AddElement('size', $size);

        if ($multiple == true)
            $this->AddElement('multiple', 'true');

        if ($disabled == true)
            $this->AddElement('disabled', 'true');
    }

    public function AddOption($name, $value=NULL, $selected=false, $disabled=false) {
        $option = $this->ForkChild('option');

        $option->AddElement('name', $name);

        if ($value == NULL)
            $option->AddElement('value', $name);
        else
            $option->AddElement('value', $value);

        if ($selected != false)
            $option->AddElement('selected', 'true');

        if ($disabled != false)
            $option->AddElement('disabled', 'true');

        $this->AddChild($option);
    }
}

class FormTextArea extends XMLement {

    public $Disabled = false;
    public $Readonly = false;

    public function __construct($name, $rows, $columns, $value=NULL) {
        parent::__construct('textarea', NULL, 'form');

        global $_CONF;

        $this->AddElement('name', $_CONF['form']['prefix'] . $name);
        $this->AddElement('rows', $rows);
        $this->AddElement('cols', $columns);

        if ($value != NULL)
            $this->AddElement('value', $value);
    }

    public function Build() {
        if ($this->Disabled != false)
            $this->AddElement('disabled', 'true');

        if ($this->Readonly != false)
            $this->AddElement('readonly', 'true');

        return $this;
    }
}

class FormError extends XMLement {

    public function __construct($message) {
      parent::__construct('error', $message, 'form');
    }

}

global $_CONF;
$var = NULL;
if (strtolower($_CONF['form']['method']) == 'post')
    $var = $_POST;
elseif (strtolower($_CONF['form']['method']) == 'get')
    $var = $_GET;
else
    ex_debug('$_CONF[form][method] has not been set, ' .
             'or has not been set to an acceptable value.');

$_FORM = Array();
global $_FORM;
if ($var)
    foreach($var as $key => $value)
        if (substr($key, 0, 5) == 'form_')
            $_FORM[substr($key, 5)] = $value;
?>
