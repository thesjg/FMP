<?php

/*

Copyright (C) 2002-2004 Ryan C. Creasey. All rights reserved.
Copyright (C) 2004 Samuel J. Greear. All rights reserved.

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

$Id$

*/

class FormDispatch {

    private $Parent;

    public $Forms;
    public $Dispatch;

    public function __construct(&$parent) {
        $this->Parent =& $parent;

        $this->Forms = array();
        $this->Dispatch = array();
    }

    public function SetDefault($form) {
        $name = $form->Name;
        foreach ($this->Forms as $key => &$value)
            if ($value[0] == $name)
                $value[2] = 'true';
    }

    public function Add($form) {
        $this->Forms[] = array($form->Name, $form, 'false');
    }

    public function OnClick($form, $action, $newform) {
        $this->Dispatch[] = array($form->Name, $action, $newform);
    }

    public function Dispatch() {
        global $_FORM;

        if (!isset($_FORM))
            foreach ($this->Forms as $key => $value)
                if ($value[2] == 'true') {
                    $this->Display($value[1]);
                    return;
                }

        foreach ($this->Forms as $fvalue) {
            if ($fvalue[0] == $_FORM['_FormName']) {
                foreach ($this->Dispatch as $dvalue) {
                    if ($dvalue[0] == $_FORM['_FormName']) {
                        if (in_array($dvalue[1], $_FORM)) {
                            if ($fvalue[1]->Valid == false) {
                                $this->Display($fvalue[1]);
                                return;
                            }
                            $this->Display($dvalue[2]);
                            return;
                        }
                    }
                }
            }
        }
    }

    private function Display($form) {
        if ($form instanceof Form) {
            $this->Parent->Data[] = $form;
        } else {
            $this->Parent->Data[] = $form;
        }
    }
}

?>
