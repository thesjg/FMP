<?php
/**
 * TODO:
 *
 * Support Etag?
 */

require('global.php');

$extension = '.swf';

if (isset($_GET['cid'])) {

    $fdci = new FlixnDatabaseComponentInstances();
    if (!$fdci->loadByComponentId($_GET['cid']))
        do_error('Could not retrieve component instance');

    $fdct = new FlixnDatabaseComponentTypes();
    if (!$fdct->loadBy('id', $fdci->type_id))
        do_error('Could not retrieve component type');

    header('Location: /' . $fdct->name . '-' . $fdct->version . $extension
           . '?cid=' . $_GET['cid'], true, 303);
    exit();

}

if (!isset($_GET['cname']) && !isset($_GET['cversion']))
    do_error('');

if ($_GET['cname'] == 'expressinstall') {
    $filename = 'components/expressinstall/expressInstall.swf';
} else {
    $filename = 'components/' . $_GET['cname'] . '/' . $_GET['cversion'] .
                '/component' . $extension;
}

$yr = 2592000; /* 30 days */
$last_modified = gmdate('D, d M Y H:i:s', filemtime($filename)) . ' GMT';

if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
    if ($last_modified == $_SERVER['HTTP_IF_MODIFIED_SINCE']) {
        header('HTTP/1.1 304 Not Modified', true, 304);
        header('Content-Length: 0', true, 304);
        exit();
    }
}

$filesize = filesize($filename);

header('Last-Modified: ' . $last_modified, true, 200);
header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$yr) . ' GMT', true, 200);
header('Pragma: public', true, 200);
header('Cache-Control: public', true, 200);
header('Content-Length: ' . $filesize, true, 200);

readfile($filename);