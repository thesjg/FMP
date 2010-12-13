<?php

class Zend_View_Helper_InstanceSelect
{
    function instanceSelect()
    {
        $fc = Zend_Controller_Front::getInstance();
        $r = $fc->getRequest();

        $controller = $r->getParam('controller');
        $action = $r->getParam('action');

        $instance = $r->getParam('instance');

$template = <<<TEMPLATE
<div id="select_menu">
    <div>
        <ul>
            %s
        </ul>
    </div>
</div>
TEMPLATE;


$option = "<li><a href='%s'>%s%s</a></li>\n";

        $type = NULL;
        if ($controller == 'uploaded')
            $type = 'uploader';
        else if ($controller == 'recorded')
            $type = 'recorder';
        else if ($controller == 'moderation')
            $type = 'moderation';
        else if ($controller == 'playback')
            $type = 'player';
        else if ($controller == 'transcode')
            $type = 'transcode';

        if ($type == NULL)
            return;

        $auth = Zend_Auth::getInstance();
        $identity = $auth->getIdentity();

        if ($type == 'moderation') {
            $uri = '/' . $controller . '/view/instance/';
            $instances = Doctrine::getTable('ModerationInstance')
                         ->findByUserId($identity['id']);
        } elseif ($type == 'transcode') {
            $uri = '/' . $controller . '/settings/profile/';
            $instances = Doctrine::getTable('TranscodeVideo')
                         ->findByUserId($identity['id']);
        } else {
            $uri = '/' . $controller . '/settings/instance/';
            $instances = Doctrine_Query::create()
                         ->select('ci.id, ci.name')
                         ->from('ComponentInstance ci, ComponentType ct, ComponentState cs')
                         ->addWhere('ct.name = \'' . $type . '\'')
                         ->addWhere('ci.type_id = ct.id')
                         ->addWhere('user_id= ' . $identity['id'])
                         ->addWhere('ci.state_id = cs.id')
                         ->addWhere("cs.state != 'INGEST'")
                         ->execute();
        }

        $options = '';
        
        if(count($instances) == 0)
        {
            $options .= sprintf("<li id='no_instance'>No Instances Exist</li>\n");
        } else {
            foreach ($instances as $key)
                $options .= sprintf($option, $uri . $key->id, '', $key->name);

        }

            return sprintf($template, $options);
        }
}