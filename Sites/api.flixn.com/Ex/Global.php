<?php
/**
 * Exhibition
 *
 * @category    Exhibition
 * @package     Base
 * 
 * @author      Samuel J. Greear
 * @copyright   Copyright (c) 2004-2006 Samuel J. Greear
 * @copyright   Copyright (c) 2007-2008 Evilcode Corporation
 * @version     $Id $
 */

/* Turn off magic quoting */
ini_set('magic_quotes_gpc', 0);
ini_set('magic_quotes_runtime', 0);

/* Say yes to error reporting */
error_reporting(E_ALL|E_STRICT);
ini_set('display_errors', true);

/* Do include's that would not otherwise get pulled in */
require_once 'Loader.php';
ExLoader::$baseDir = dirname(__FILE__) . '/../';
ExLoader::registerAutoload();

/*
@define('SITE_BASE', dirname(__FILE__) . '/../..');
@define('SITE_LIB', SITE_BASE . '/lib/php');

if (!isset($_CONF['debug']))
    $_CONF['debug'] = true;
if ($_CONF['debug'] == true) {
    error_reporting(E_ALL);
    if ($_CONF['debug_level'] < 1)
        $_CONF['debug_level'] = 1;
}
*/