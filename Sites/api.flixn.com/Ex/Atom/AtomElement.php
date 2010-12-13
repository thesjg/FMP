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

class AtomElement extends XMLement {

    private $id;
    private $rights;
    private $title;
    private $updated;

    private $authors;
    private $categories;
    private $contributors;
    private $links;

    const TEXT_TYPE_TEXT = 1;
    const TEXT_TYPE_HTML = 2;
    const TEXT_TYPE_XHTML = 3;

    const PERSON_TYPE_AUTHOR = 1;
    const PERSON_TYPE_CONTRIBUTOR = 2;

    public function __construct($name) {
        parent::__construct($name, NULL, 'atom');

        $this->id = NULL;
        $this->rights = NULL;
        $this->title = NULL;
        $this->updated = NULL;

        $this->authors = array();
        $this->contributors = array();
        $this->categories = array();
        $this->links = array();
    }

    public function addAuthor($name, $uri=NULL, $email=NULL) {
        return $this->addPerson(AtomElement::PERSON_TYPE_AUTHOR, $name, $uri, $email);
    }

    public function addContributor($name, $uri=NULL, $email=NULL) {
        return $this->addPerson(AtomElement::PERSON_TYPE_CONTRIBUTOR, $name, $uri, $email);
    }

    public function addCategory($term, $scheme=NULL, $label=NULL) {
        $this->categories[] = array('term' => $term, 'scheme' => $scheme, 'label' => $label);
    }

    public function addLink($link) {
        if (!$link instanceof AtomLink)
            throw new AtomException('Link must be an instance of AtomLink');

        $this->links[] = $link;
    }

    private function addPerson($type, $name, $uri=NULL, $email=NULL) {
        if ($type != AtomElement::PERSON_TYPE_AUTHOR &&
            $type != AtomElement::PERSON_TYPE_CONTRIBUTOR)
            throw new AtomException('Person type must be Author or Contributor');

        $person = array('name' => $name, 'uri' => $uri, 'email' => $email);

        if ($type == AtomElement::PERSON_TYPE_AUTHOR)
            $this->authors[] = $person;
        elseif ($type == AtomElement::PERSON_TYPE_CONTRIBUTOR)
            $this->contributors[] = $person;
    }

    // <id>http://example.com/blog/1234</id>
    public function setId($id) {
        $this->id = $id;
    }

    public function setRights($data, $type=AtomElement::TEXT_TYPE_TEXT) {
        $this->rights = array('type' => $type, 'data' => $data);
    }

    // <title>Atom-Powered Robots Run Amok</title>
    public function setTitle($data, $type=AtomElement::TEXT_TYPE_TEXT) {
        $this->title = array('type' => $type, 'data' => $data);
    }

    // <updated>2003-12-13T18:30:02-05:00</updated>
    public function setUpdated($updated) {
        if ($updated === NULL)
            $updated = time();

        if (!is_int($updated))
            $this->updated = $updated;
        else
              // XXX: UTC/Timezone checking, etc.
            $this->updated = date(DATE_ATOM, $updated);
    }

    protected function createCategory($data) {
        if (!is_array($data))
            throw new AtomException('CreateCategory expects an array for argument $data');

        $term = $data['term'];
        $scheme = $data['scheme'];
        $label = $data['label'];

        $node = $this->forkChild('category');
        $node->setAttribute('term', $term);

        if ($scheme !== NULL)
            $node->setAttribute('scheme', $scheme);

        if ($label !== NULL)
            $node->setAttribute('label', $label);

        return $node;
    }

    protected function createPerson($nodeName, $data) {
        if (!is_array($data))
            throw new AtomException('CreatePerson expects an array for argument $data');

        $name = $data['name'];
        $uri = $data['uri'];
        $email = $data['email'];

        $node = $this->forkChild($nodeName);
        $node->addChild($node->forkChild('name', $name));

        if ($uri !== NULL)
            $node->addChild($node->forkChild('uri', $uri));

        if ($email !== NULL)
            $node->addChild($node->forkChild('email', $email));

        return $node;
    }

    protected function createText($nodeName, $data) {
        if (!is_array($data))
            throw new AtomException('CreateText expects an array for argument $data');

        $type = $data['type'];
        $text = $data['data'];

        if ($type != AtomElement::TEXT_TYPE_TEXT &&
            $type != AtomElement::TEXT_TYPE_HTML &&
            $type != AtomElement::TEXT_TYPE_XHTML)
            throw new AtomException('Type of text must be specified as TEXT, HTML or XHTML');

        $node = $this->forkChild($nodeName);

          // when building any text type, title, summary, content, rights
          // if type == HTML, do entity escaping
          // if type == xhtml, wrap in div, <div xmlns="http://www.w3.org/1999/xhtml"/>
        switch ($type) {
            case AtomElement::TEXT_TYPE_TEXT:
                $node->setValue($text);
                $node->setAttribute('type', 'text');
                break;
            case AtomElement::TEXT_TYPE_HTML:
                $node->setValue(htmlentities($text));
                $node->setAttribute('type', 'html');
                break;
            case AtomElement::TEXT_TYPE_XHTML:
                  // XXX
                $node->setValue('<div xmlns="http://www.w3.org/1999/xhtml"/>' . $text . '</div>');
                $node->setAttribute('type', 'xhtml');
                break;
        }

        return $node;
    }

    public static function consume($data) {
    }

    public function build() {

        if ($this->id !== NULL)
            $this->addChild($this->forkChild('id', $this->id));

        if ($this->rights !== NULL)
            $this->addChild($this->createText('rights', $this->rights));

        if ($this->title !== NULL)
            $this->addChild($this->createText('title', $this->title));

        if ($this->updated !== NULL)
            $this->addChild($this->forkChild('updated', $this->updated));

        foreach ($this->authors as $author)
            $this->addChild($this->createPerson('author', $author));

        foreach ($this->contributors as $contributor)
            $this->addChild($this->createPerson('contributor', $contributor));

        foreach ($this->categories as $category)
            $this->addChild($this->createCategory($category));

        foreach ($this->links as $link)
            $this->addChild($link->build());

        return $this;
    }
}