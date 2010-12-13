<?php

/*

Copyright (C) 2004 Samuel J. Greear. All rights reserved.

$Id$

*/

class Navigation extends XMLement {

    private $Order;

    const ORDER_EARLY = 1;
    const ORDER_LATE = 2;

    public function __construct($style) {
        parent::__construct('navigation', NULL, 'navigation');
        $this->SetAttribute('style', $style);
    }

    public function AddElement($value) {
        parent::AddElement('element', $value);
    }

    public function AddItem($title=NULL, $address=NULL, $summary=NULL, $icon=NULL) {
        $this->AddChild(new NavigationItem($title, $address, $summary, $icon));
    }

    public function AddHeader($name) {
        $element = $this->ForkChild('header');
        $element->AddElement('heading', $name);
        $this->AddChild($element);
    }

    public function AddText($text) {
        $element = $this->ForkChild('text');
        $element->AddElement('text', $text);
        $this->AddChild($element);
    }

    public function AddBox($text) {
        $element = $this->ForkChild('box');
        $element->AddElement('box', $text);
        $this->AddChild($element);
    }

    public function HintOrder($hint) {
        if ($hint != Navigation::ORDER_EARLY || $hint != Navigation::ORDER_LATE)
            throw new Exception('Invalid order hint specified.');

        $this->Order = $hint;
    }
}


class NavigationItem extends XMLement {

    private $Title = NULL;
    private $Address = NULL;
    private $Summary = NULL;
    private $Data = NULL;
    private $Icon = NULL;

    public function __construct($title=NULL, $address=NULL, $summary=NULL, $icon=NULL) {
        $this->Title = $title;
        $this->Address = $address;
        $this->Summary = $summary;
        $this->Icon = $icon;

        $this->Create();
    }

    private function Create() {
        parent::__construct('item', NULL, 'navigation');

        if ($this->Title != NULL)
            $this->AddElement('title', $this->Title);

        if ($this->Address != NULL)
            $this->AddElement('address', $this->Address);

        if ($this->Summary != NULL)
            $this->AddElement('summary', $this->Summary);

        if ($this->Icon != NULL)
            $this->AddElement('icon', $this->Icon);
    }
}

?>