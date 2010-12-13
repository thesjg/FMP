<?php

/*

Copyright (C) 2004-2005 Samuel J. Greear. All rights reserved.
Copyright (C) 2004-2005 Ryan C. Creasey. All rights reserved.

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

class Table extends XMLement {

    protected $Body = NULL;
    protected $Header = NULL;
    protected $Footer = NULL;
    protected $Row = NULL;

    private $Heading = false;
    private $Footing = false;
    private $Sorted;
    private $SortOrder;
    private $Cell;
    private $SortCell = NULL;
    private $Pageable = false;
    private $PageRows = 0;

    const SORT_TEXT = 1;
    const SORT_NUMBER = 2;

    public function __construct($title, $overview=NULL, $css_class=NULL) {
        parent::__construct('table', NULL, 'table');

        $this->AddElement('title', $title);
        if ($overview != NULL)
            $this->AddElement('overview', $overview);
        if ($css_class != NULL)
            $this->AddElement('css_class', $css_class);

        $this->Body = $this->ForkChild('body');

        $this->Sorted = false;
        $this->SortOrder = 'ascending';
    }

    public function SetPageable($tf, $rows=20) {
        if ($tf != false && $tf != true)
             throw new Exception('$tf must be boolean.');

        $this->Pageable = $tf;
        $this->PageRows = $rows;
    }

    public function SetHeader($css_class=NULL, $span=1) {
        $this->Heading = true;
        $this->Header = $this->ForkChild('header');

        if ($css_class != NULL)
            $this->Header->AddElement('css_class', $css_class);

        if ($span > 1)
            $this->Header->AddElement('span', $span);
    }

    public function SetFooter($css_class=NULL, $span=1) {
        $this->Footing = true;
        $this->Footer = $this->ForkChild('footer');

        if ($css_class != NULL)
            $this->Footer->AddElement('css_class', $css_class);

        if ($span > 1)
            $this->Footer->AddElement('span', $span);
    }

    public function NewRow($css_class=NULL) {
        $this->Cell = 0;

        if ($this->Row != NULL)
            $this->Body->AddChild($this->Row);

        $this->Row = $this->ForkChild('row');
        $this->Heading = false;
        $this->Footing = false;

        if ($css_class != NULL)
            $this->Row->AddElement('css_class', $css_class);
    }

    public function AddCell($data, $css_class=NULL, $sortable=false,
                            $sortstyle=Table::SORT_TEXT) {

        if ($sortable == true && $this->Heading == false)
            throw new Exception('Only cells in a tables heading may be set sortable.');

        $this->Cell += 1;
        global $_POST;
        if (isset($_POST['sortby']) && $_POST['sortby'] == $data && $sortable == true)
            $this->SortCell = $this->Cell;

        $cell = $this->ForkChild('cell');
        if ($data instanceof XMLement)
            $cell->AddChild($data);
        else
            $cell->AddElement('data', $data);

        if ($css_class != NULL)
            $cell->AddElement('css_class', $css_class);

        if ($sortable == true) {
            $cell->SetAttribute('sortable', 'true');
            if ($sortstyle == Table::SORT_TEXT)
                $cell->SetAttribute('sortstyle', 'text');
            else
                $cell->SetAttribute('sortstyle', 'number');
        }

        if ($this->Cell == $this->SortCell) {
            if (isset($_POST['sortorder']) && $_POST['sortorder'] == 'descending')
                $this->SortOrder = 'descending';
            $cell->SetAttribute('sorted', $this->SortOrder);
        }

        if ($this->Heading == true)
            $this->Header->AddChild($cell);
        elseif ($this->Footing == true)
            $this->Footer->AddChild($cell);
        else
            $this->Row->AddChild($cell);
    }

    public function Build() {
        $this->NewRow();

        if ($this->Header != NULL)
            $this->AddChild($this->Header);

        if ($this->Pageable == true)
            $this->SetAttribute('pageable', $this->PageRows);

        $this->AddChild($this->Body);

        if ($this->Footer != NULL)
            $this->AddChild($this->Footer);

        return $this;
    }
}

?>
