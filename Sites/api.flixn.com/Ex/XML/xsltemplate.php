#!/usr/local/bin/php
<?php

$xml = new DOMDocument();
$xml->load('test.xml');

$proc1 = new XSLTProcessor();
$proc1->importStyleSheet(DOMDocument::load('test.xsl'));

$proc2 = new XSLTemplate();
$proc2->loadStyleSheet('test.tpl');

print "XSL Test:\n";
echo $proc1->transformToXML($xml);

print "\nXSL Shorthand Test:\n";
echo $proc2->transformToXML($xml);

class XSLTemplate extends XSLTProcessor {

    public $OpeningDelimiter;
    public $ClosingDelimiter;

    public $Prefix;
    public $Suffix;

    protected $Patterns = NULL;
    protected $Callbacks;
    protected $Replacements;

    const REGTYPE_CALLBACK = 1;
    const REGTYPE_REPLACE = 2;

    public function loadStylesheet($filename) {
        if ($this->Patterns === NULL)
            $this->Setup();

        $file_contents = file_get_contents($filename);
        $parsed = $this->Parse($file_contents);

        $xsl = new DOMDocument();
//        $xsl->documentURI = $filename;

print "\n\nPARSED: \n" . $parsed . "\n\n";
        $xsl->loadXML($parsed);
        $this->importStylesheet($xsl);
    }

    public function Setup() {
        $this->OpeningDelimiter = '<@';
        $this->ClosingDelimiter = '@>';

        $this->Prefix = '<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">';
        $this->Suffix = '</xsl:stylesheet>';

        $this->Patterns = array();
        $this->Callbacks = array();
        $this->Replacements = array();

        $this->AddReg('choose', 'choose', '<xsl:choose>');
        $this->AddReg('end-choose', '\/choose', '</xsl:choose>');

        $this->AddReg('for-each', 'for-each (.+?)',
                      '<xsl:for-each select="${1}">');
        $this->AddReg('end-for-each', '\/for-each', '</xsl:for-each>');

        $this->AddReg('if', 'if (.+?)', '<xsl:if test="${1}">');
        $this->AddReg('end-if', '\/if', '</xsl:if>');

        $this->AddReg('otherwise', 'otherwise', '<xsl:otherwise>');
        $this->AddReg('end-otherwise', '\/otherwise', '</xsl:otherwise>');

        $this->AddReg('template', 'template (.+)',
                      '<xsl:template match="${1}">');
        $this->AddReg('end-template', '\/template', '</xsl:template>');

        $this->AddReg('value-of', 'value-of (.+)[\s*]([\s]{1})?(\w+)?',
                      array($this, CallbackValueOf),
                      XSLTemplate::REGTYPE_CALLBACK);

        $this->AddReg('when', 'when (.+?)', '<xsl:when test="${1}">');
        $this->AddReg('end-when', '\/when', '</xsl:when>');
    }

    public function AddReg($name, $pattern, $action,
                           $type=XSLTemplate::REGTYPE_REPLACE) {

        $opening = '/' . $this->OpeningDelimiter . '\s*';
        $closing = '\s*' . $this->ClosingDelimiter . '/';

        $this->Patterns[$name] = array('type' => $type,
                                       'pattern' => $opening . $pattern .
                                                    $closing);

        if ($type == XSLTemplate::REGTYPE_CALLBACK)
            $this->Callbacks[$name] = $action;
        else
            $this->Replacements[$name] = $action;
    }

    public function Parse($text) {

        $text = $this->Prefix . $text . $this->Suffix;

        foreach ($this->Patterns as $k => $v) {
            if ($v['type'] == XSLTemplate::REGTYPE_CALLBACK) {
                $text = preg_replace_callback($v['pattern'],
                                              $this->Callbacks[$k], $text);
            } else {
                $text = preg_replace($v['pattern'], $this->Replacements[$k],
                                     $text);
            }
        }

        return $text;
    }

    protected function CallbackValueOf($matches) {
        $ret = '<xsl:value-of select="' . $matches[1] . '"';
        if ($matches[3])
            $ret .= ' disable-output-escaping="' . $matches[3] . '"';
        $ret .= ' />';
        return $ret;
    }
}