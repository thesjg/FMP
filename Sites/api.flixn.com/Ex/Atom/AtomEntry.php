<?php
/**
 * Exhibition
 *
 * @category    Exhibition
 * @package     Atom
 * 
 * @author      Samuel J. Greear
 * @copyright   Copyright (c) 2005-2006 Samuel J. Greear
 * @copyright   Copyright (c) 2008 Evilcode Corporation
 * @version     $Id $
 */

// must have a link if no content

class AtomEntry extends AtomElement {

    public $content;
    public $published;
    public $summary;

    public function __construct() {
        parent::__construct('entry');

        $this->content = NULL;
        $this->published = NULL;
        $this->summary = NULL;
    }

    public function addContent($data, $type=AtomElement::TEXT_TYPE_TEXT) {
        $this->content = array('type' => $type, 'data' => $data);
    }

    public function addSummary($data, $type=AtomElement::TEXT_TYPE_TEXT) {
        $this->summary = array('type' => $type, 'data' => $data);
    }

      // Time of the initial creation or first availability of the entry
    public function setPublished($published) {
        if (!is_int($published))
            $this->published = $published;
        else
              // XXX: UTC/Timezone checking, etc.
            $this->published = date(DATE_ATOM, $published);
    }

      // Copy of all child elements of feed sans entry elements if we copied this entry
    public function setSource() {
        // XXX
    }

    public static function consume($data) {
    }

    public function build() {

        if ($this->content !== NULL)
            $this->addChild($this->createText('content', $this->content));

        if ($this->summary !== NULL)
            $this->addChild($this->createText('summary', $this->summary));

        if ($this->published !== NULL)
            $this->addChild($this->forkChild('published', $this->published));

        parent::build();

        return $this;
    }
}