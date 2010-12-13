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

// limited to one alternate per type and hreflang. A feed should contain a link back to the feed itself

class AtomFeed extends AtomElement {

    public $icon;
    public $logo;
    public $subtitle;

    public $entries;

    public function __construct() {
        parent::__construct('feed');

        $this->icon = NULL;
        $this->logo = NULL;
        $this->subtitle = NULL;

        $this->entries = array();
    }

    public function addEntry($entry) {
        if (!$entry instanceof AtomEntry)
            throw new AtomException('Entries must be instances of AtomEntry class');

        $this->entries[] = $entry;
    }

    public function setIcon($uri) {
        $this->icon = $uri;
    }

    public function setLogo($uri) {
        $this->logo = $uri;
    }

    public function setSubtitle($data) {
        $this->subtitle = $data;
    }

    public static function consume($data) {
    }

    public function build() {
        if ($this->icon !== NULL)
            $this->addElement('icon', $this->icon);

        if ($this->logo !== NULL)
            $this->addElement('logo', $this->logo);

        if ($this->subtitle !== NULL)
            $this->addElement('subtitle', $this->subtitle);

        $node = $this->forkChild('generator', 'Exhibition');
        $node->setAttribute('uri', 'http://evilprojects.com/exhibition/');
        $node->setAttribute('version', '1.0');
        $this->addChild($node);

        parent::build();

        foreach ($this->entries as $entry)
            $this->addChild($entry->build());

        return $this;
    }
}