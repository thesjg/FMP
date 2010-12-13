<?php

$fdcps = new FlixnDatabaseComponentPlayerSettings();
$fdcps->loadByInstanceId($fdci->id);

$settings_tpl = <<<TPL
<?xml version="1.0" encoding="UTF-8"?>
<settings>
 <id>%s</id>
 <key>%s</key>
 <style>%s</style>
 <features>
  %s
 </features>
</settings>
TPL;

$settings_feature_tpl = <<<TPL
<feature>%s</feature>
TPL;

$settings_features = '';

if ($fdcps->embed == 't')
    $settings_features .= sprintf($settings_feature_tpl, 'embed');

if ($fdcps->share == 't')
    $settings_features .= sprintf($settings_feature_tpl, 'share');

if ($fdcps->email == 't')
    $settings_features .= sprintf($settings_feature_tpl, 'email');

if ($fdcps->sms == 't')
    $settings_features .= sprintf($settings_feature_tpl, 'sms');

if ($fdcps->info == 't')
    $settings_features .= sprintf($settings_feature_tpl, 'info');

if ($fdcps->fullscreen == 't')
    $settings_features .= sprintf($settings_feature_tpl, 'fullscreen');

if ($fdcps->popout == 't')
    $settings_features .= sprintf($settings_feature_tpl, 'popout');

if ($fdcps->lighting == 't')
    $settings_features .= sprintf($settings_feature_tpl, 'lighting');

if ($fdcps->autoplay == 't')
    $settings_features .= sprintf($settings_feature_tpl, 'autoplay');


printf($settings_tpl, $_GET['cid'], $fdci->component_key, 'default', $settings_features);