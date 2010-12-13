<?php

class Zend_View_Helper_ComponentName
{
    function componentName()
    {
        $fc = Zend_Controller_Front::getInstance();
        $r = $fc->getRequest();

        $controller = $r->getParam('controller');
        $action = $r->getParam('action');
        $instance = $r->getParam('instance');
        $profile = $r->getParam('profile');
        $start = $r->getParam('start');
        
        if (isset($instance) || isset($profile) || isset($start))
        {
            switch ($controller)
            {
                case "moderation":
                    $pre = Doctrine::getTable('ModerationInstance')
                                ->findOneById($instance);
                    $name = $pre['name'];
                    break;
                
                case "recorded":
                    $pre = Doctrine::getTable('ComponentInstance')
                                ->findOneById($instance);
                    $name = $pre['name'];
                    $controller = "recorder";
                    break;
                
                case "uploaded":
                    $pre = Doctrine::getTable('ComponentInstance')
                                ->findOneById($instance);
                    $name = $pre['name'];
                    $controller = "uploader";
                    break;
                
                case "transcode":
                    $pre = Doctrine::getTable('TranscodeVideo')
                                ->findOneById($profile);
                    $name = $pre['name'];
                    break;
                
                case "playback":
                    $pre = Doctrine::getTable('ComponentInstance')
                                ->findOneById($instance);
                    $name = $pre['name'];
                    $controller = "player";
                    break;
                
                case "statistics":
                    $controller = "statistics";
                    break;
                
            }
            
            $sub = "instance";
            
            if($controller == "transcode" || $controller == "moderation")
                $sub = "profile";
                
            $output =  "Working with <strong>" . ucwords($controller) . "</strong> $sub: <strong id='instance_name_top'>$name</strong>";
            
            if($controller == "statistics")
            {
                $output = "<span style='font-size: 11px; '>Viewing dates " . $r->getParam('start') . " through " . $r->getParam('end') . ".</span>";
            }
        
            return $output;
        } else {
            return false;
        }
    }
}
?>