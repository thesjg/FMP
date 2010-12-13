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

$Id: form.php 43 2004-11-24 05:06:28Z sjg $

*/

$validations = Array();
global $validations;
// preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*$/i', $email)
$validations['email'] = Array('^[a-zA-Z0-9._-]+@[a-zA-Z0-9-]+\.[a-zA-Z.]{2,6}$',
                              "The electronic mail address you have provided " .
                              "is not valid, it must be of the format " .
                              "'name@domain.ext'.");
$validations['username'] = Array('^[a-zA-Z0-9]{6,40}$',
                                 'The username you have provided is not valid, valid ' .
                                 'usernames are between 6 and 40 characters long, and ' .
                                 'contain only alphanumeric characters (a-z, A-Z, 0-9). ' .
                                 'All usernames are case-sensitive.');

$NameId = 0;
global $NameId;

/**
 * EasyForm
 *
 * This is the EasyForm class, more documentation to come.
 */
class EasyForm extends Form {

      /**
       * @var string Name of the EasyForm [OPTIONAL]
       */
    public $Name;

    protected $Group;
    protected $Element = NULL;
    protected $Select = NULL;
    private $SelectedValue = NULL;

    protected $Validations;

      /**
       * Constructor
       *
       * @param string $action Action, or where to POST/GET the form to
       * @param string $groupname Name of the default, or first, form group
       * @param string $name Name of the EasyForm
       * @return void
       */
    public function __construct($action, $groupname=NULL, $name=NULL, $target=NULL) {
        global $NameId, $validations;

        if ($name == NULL) {
            $NameId++;
            $name = 'EasyForm' . (string)$NameId;
        }

        $this->Name = $name;

        parent::__construct($name, $action, NULL, NULL, NULL, $target);
        $this->Group = new FormGroup($groupname);

        $this->Validations = Array();
        foreach ($validations as $key => $value)
            $this->Validations[$key] = $value;
    }

      /**
       * AddValidation - Add validation to current element based on regular
       * expression in global validations array.
       *
       * @param string $name Name of validation in global validations array
       * @return void
       */
    private function AddValidation($name) {
        foreach ($this->Validations as $key => $value)
            if ($name == $key) {
                $this->Element->AddElement('validation', $value[0]);
                $this->Element->AddElement('validation_error', $value[1]);
            }
    }

    public function AddSelectChild() {
        if ($this->Select != NULL) {
            $this->Element->AddChild($this->Select);
            $this->Select = NULL;
        }
    }

      /**
       * NewElement
       *
       * @param string $title
       * @param string $caption
       * @param boolean $required
       * @param string $class
       * @return void
       */
    public function NewElement($title=NULL, $caption=NULL, $required=false, $class=NULL) {
        $this->AddSelectChild();

        if ($this->Element != NULL)
            $this->Group->AddChild($this->Element);

        $this->Element = new FormElement($title, $caption, $required);
        if ($class !== NULL) {
            $this->Element->SetAttribute('class', $class);
        }
    }

      /**
       * NewGroup
       *
       * @param string $groupname
       * @return void
       */
    public function NewGroup($groupname) {
        $this->NewElement();
        $this->Element = NULL;

        $this->AddChild($this->Group);
        $this->Group = new FormGroup($groupname);
    }

      /**
       * AddText
       *
       * @param string $name
       * @param string $value
       * @param int $maxlength
       * @param string $class
       * @return void
       */
    public function AddText($name, $value=NULL, $maxlength=255, $class=NULL) {
        $this->AddSelectChild();

        $input = new FormInput('text', $name, $value, $maxlength);
        $this->AddValidation($name);
        if ($class !== NULL) {
            $input->SetAttribute('class', $class);
        }
        $this->Element->AddChild($input->Build());
    }

      /**
       * AddPassword
       *
       * @param string $name
       * @param string $value
       * @param string $class
       * @return void
       */
    public function AddPassword($name, $value=NULL, $class=NULL) {
        $this->AddSelectChild();

        $input = new FormInput('password', $name, $value);
        if ($class !== NULL) {
            $input->SetAttribute('class', $class);
        }
        $this->AddValidation($name);
        $this->Element->AddChild($input->Build());
    }

      /**
       * AddHidden
       *
       * @param string $name
       * @param string $value
       * @param string $class
       * @return void
       */
    public function AddHidden($name, $value=NULL, $class=NULL) {
        $this->AddSelectChild();

        $input = new FormInput('hidden', $name, $value);
        if ($class !== NULL) {
            $input->SetAttribute('class', $class);
        }
        $this->Element->AddChild($input->Build());
    }

      /**
       * AddTextArea
       *
       * @param string $name
       * @param string $value
       * @param int $rows
       * @param int $columns
       * @param string $class
       * @return void
       */
    public function AddTextArea($name, $value=NULL, $rows=6, $columns=65, $class=NULL) {
        $this->AddSelectChild();

        $textarea = new FormTextArea($name, $rows, $columns, $value);
        if ($class !== NULL) {
            $textarea->SetAttribute('class', $class);
        }
        $this->Element->AddChild($textarea->Build());
    }

      /**
       * AddCheckBox
       *
       * @param string $name
       * @param string $value
       * @param boolean $checked
       * @param string $class
       * @return void
       */
    function AddCheckBox($name, $value, $checked=false, $class=NULL) {
        $this->AddSelectChild();

        $input = new FormInput('checkbox', $name, $value);
        $input->Checked = $checked;
        if ($class !== NULL) {
            $input->SetAttribute('class', $class);
        }
        $this->Element->AddChild($input->Build());
    }

      /**
       * AddRadioBox
       *
       * @param string $name
       * @param string $value
       * @param boolean $checked
       * @return void
       */
    function AddRadioBox($name, $value, $selected=false) {
        $this->AddSelectChild();

        $input = new FormInput('radio', $name, $value);
        $input->Checked = $selected;
        $this->Element->AddChild($input->Build());
    }

      /**
       * AddFile
       *
       * @param string $name
       * @param string $class
       * @return void
       */
    function AddFile($name, $class=NULL) {
        $this->AddSelectChild();

        $input = new FormInput('file', $name);
        if ($class !== NULL) {
            $class->SetAttribute('class', $class);
        }
        $this->Element->AddChild($input->Build());
    }

      /**
       * AddImage
       *
       * @param string $name
       * @param string $class
       * @return void
       */
    function AddImage($name, $class=NULL) {
        $this->AddSelectChild();

        $input = new FormInput('image', $name);
        if ($class !== NULL) {
            $class->SetAttribute('class', $class);
        }
        $this->Element->AddChild($input->Build());
    }

      /**
       * NewSelect
       *
       * @param string $name
       * @param string $selected_value
       * @param string $class
       * @return void
       */
    public function NewSelect($name, $selected_value=NULL, $class=NULL) {
        $this->AddSelectChild();

        $this->Select = new FormSelect($name);
        $this->SelectedValue = $selected_value;

        if ($class !== NULL) {
            $this->Select->SetAttribute('class', $class);
        }
    }

      /**
       * AddSelectOption
       *
       * @param string $name
       * @param string $value
       * @return void
       */
    public function AddSelectOption($name, $value=NULL) {
        if ($this->SelectedValue != NULL && $value == $this->SelectedValue)
            $this->Select->AddOption($name, $value, true);
        elseif ($this->SelectedValue != NULL && $name == $this->SelectedValue)
            $this->Select->AddOption($name, $value, true);
        else
            $this->Select->AddOption($name, $value);
    }

      /**
       * AddSubmit
       *
       * @param string $value
       * @param string $name
       * @param string $class
       * @return void
       */
    public function AddSubmit($value='Submit', $name='submit', $class=NULL) {
        $this->AddSelectChild();

        $input = new FormInput('submit', $name, $value);
        if ($class !== NULL) {
            $input->SetAttribute('class', $class);
        }
        $this->Element->AddChild($input->Build());
    }

      /**
       * Build
       *
       * @return EasyForm
       */
    public function Build() {
        $this->NewElement();
        $this->Element = NULL;

        $this->AddChild($this->Group);
        $this->Validate();

        return $this;
    }
}

?>
