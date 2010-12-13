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

class AtomLink extends XMLement {

    private $uri;
    private $rel;
    private $type; // Valid MIME type per RFC 4288
    private $lang;
    private $title;
    private $length;

    const REL_ALTERNATE = 1; // Alternate representation, permlink to html version++
    const REL_ENCLOSURE = 2; // Related resource that is potentially large (audio/video/++)
    const REL_RELATED = 3;   // Document that is somehow related
    const REL_SELF = 4;      // The feed itself
    const REL_VIA = 5;       // Source of the information provided in entry

    public function __construct($uri, $rel=NULL, $type=NULL, $lang=NULL, $title=NULL, $length=NULL) {
        parent::__construct('link', NULL, 'atom');

        $this->uri = $uri;

        if ($rel !== NULL)
            $this->setRel($rel);

        if ($type !== NULL)
            $this->setType($type);

        if ($lang !== NULL)
            $this->setLang($lang);

        if ($title !== NULL)
            $this->setTitle($title);

        if ($length !== NULL)
            $this->setLength($length);
    }

    public function setRel($rel) {
        if ($rel != AtomLink::REL_ALTERNATE && $rel != AtomLink::REL_ENCLOSURE &&
            $rel != AtomLink::REL_RELATED && $rel != AtomLink::REL_SELF && $rel != AtomLink::REL_VIA)
            throw new AtomException('Rel must be of a type defined by the Atom specification (alternate, enclosure, related, self or via)');

        $this->rel = $rel;
    }

    public function setType($type) {
        $this->type = $type;
    }

    public function setLang($lang) {
        $this->lang = $lang;
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function setLength($length) {
        if (!is_numeric($length))
            throw new AtomException('Length must be a numeric value');

        $this->length = (int)$length;
    }

    public static function consume($data) {
        // parse atom linkage via dom
        $atomlink = new AtomLink();
    }

    public function build() {
        $this->setAttribute('href', $this->uri);

        if ($this->rel !== NULL)
            $this->setAttribute('rel', $this->rel);

        if ($this->type !== NULL)
            $this->setAttribute('type', $this->type);

        if ($this->lang !== NULL)
            $this->setAttribute('hreflang', $this->lang);

        if ($this->title !== NULL)
            $this->setAttribute('title', $this->title);

        if ($this->length !== NULL)
            $this->setAttribute('length', $this->length);

        return $this;
    }
}