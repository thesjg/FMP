<?php
/**
 * Exhibition
 *
 * @category    Exhibition
 * @package     Services
 *
 * @author      Samuel J. Greear
 * @copyright   Copyright (c) 2008 Evilcode Corporation
 * @version     $Id $
 */

/**
 * ExServicesSerializer
 */
class ExServicesSerializer {

    private $_basicTypes = array('string', 'int', 'boolean');

    private $_value;
    private $_type;
    private $_arr;
    private $_comment;

    private $_output;

    public function __construct($value, $type, $arr, $comment) {

        $this->_value = $value;
        $this->_type = $type;
        $this->_arr = $arr;
        $this->_comment = $comment;

        if (in_array($this->_type, $this->_basicTypes))
            if ($this->_arr === true)
                $this->_output = $this->constructBasicArray();
            else
                $this->_output = $this->constructBasic();
        else
            $this->_output = $this->constructObject();
    }

    public function setOutputType($type) {

    }

    public function serialize() {
        return $this->_output->render();
    }

    private function constructBasic() {

        if ($this->_type == 'boolean')
            $this->_value = ($this->_value) ? 'true' : 'false';

        if ($this->_comment == '')
            throw new ExServicesException(ExServicesErrors::UNDEFINED_METHOD_RETURN);

        $r = new ExXMLElement($this->_comment, $this->_value);

        return $r;
    }

    private function constructBasicArray() {

        list($base_tag, $tag) = split(':', $this->_comment);

        $r = new ExXMLElement($base_tag);

        foreach ($this->_value as $value) {
            $r->addChild($r->forkChild($tag, $value));
        }

        return $r;
    }

    private function constructObject() {

        $r = new ExXMLElement($this->_comment);

        foreach ($this->_value as $key => $value) {
            if (is_array($value)) {

                $rx = $r->forkChild($key);

                foreach ($value as $nvalue) {
                    $rxn = $rx->forkChild(substr($key, 0, -1));

                    foreach ($nvalue as $keyx => $valuex) {
                        $rxn->addChild($rxn->forkChild($keyx, $valuex));
                    }

                    $rx->addChild($rxn);
                }

                $r->addChild($rx);

            } else {
                $r->addChild($r->forkChild($key, $value));
            }
        }

        return $r;
    }
}