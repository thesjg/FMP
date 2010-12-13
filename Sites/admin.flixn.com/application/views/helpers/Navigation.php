<?php

class Zend_View_Helper_Navigation
{
function navigation()
{
$template = <<<TPL
 <a %s href="%s" title="%s">%s</a> |
TPL;

        
        $fc = Zend_Controller_Front::getInstance();
        $r = $fc->getRequest();
        
        $controller = $r->getParam('controller');
        $action = $r->getParam('action');
        $instance = $r->getParam('instance');


        $pages = array('index' => array('Home', '/', 'Home'),
                    'account' => array('My Account', '/account', ''),
                    'uploaded' => array('Uploaded', '/uploaded', ''),
                    'recorded' => array('Recorded', '/recorded', ''),
                    'playback' => array('Playback', '/playback', ''),
                    'moderation' => array('Moderation', '/moderation', ''),
                    'transcode' => array('Transcode', '/transcode', ''));
        
        $psub = array('index' => array(
                      ),
                      'account' => array(
                        ' ' => array(' ', '/account', '')
                      ),
                      'uploaded' => array(
                        'create' => array('Create Instance', '/create', ''),
                        'settings' => array('Settings', '/settings', ''),
                      ),
                      'recorded' => array(
                        'create' => array('Create Instance', '/create', ''),
                        'settings' => array('Settings', '/settings', ''),
                        
                      ),
                      'playback' => array(
                        'create' => array('Create Instance', '/create', ''),
                        'settings' => array('Settings', '/settings', ''),
                      ),
                      'moderation' => array(
                        'create' => array('Create Instance', '/create', '')
                      ),
                      'transcode' => array(
                        'index' => array('Index', '/index', ''),
                        'create' => array('Create Profile', '/create', '')
                      )
                      );

        $output = '';
        $items = '';
        $sub = '';
        
        if (!array_key_exists($controller, $pages))
            return $output;
        
        foreach ($psub[$controller] as $key => $value) {

            $selected = '';
            if ($action == $key)
                $selected = ' id="nav_sub_selected"';
                
                
            $link = $pages[$controller][1] . $value[1];
            if ($instance)
                $link .= '/instance/' . $instance;

            $sub .= sprintf($template, $selected, $link, $value[2], $value[0]);
        }
        
        
        return substr_replace($sub,"",-1);
    }
}