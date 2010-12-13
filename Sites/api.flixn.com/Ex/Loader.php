<?php
/**
 * Exhibition
 *
 * @category    Exhibition
 * @package     Base
 * 
 * @author      Samuel J. Greear
 * @copyright   Copyright (c) 2008 Evilcode Corporation
 * @version     $Id $
 */

/**
 * ExLoader
 *
 * @todo: Document internally
 */
class ExLoader {
    
    public static $baseDir;
    
    public static function loadClass($class) {        
        $offset = 0;
        $split_path = array();
        for ($i = 1; $i < strlen($class); $i++) {
            if (ctype_upper($class{$i})) {
                if ($i > 2 && ctype_upper($class{$i+1})) {
                    if (!ctype_upper($class{$i-1})) {
                        $split_path[] = substr($class, $offset, $i-$offset);
                        $offset = $i;
                    }
                
                    for ($j = $i+1; $j < strlen($class); $j++) {
                        if (!ctype_upper($class{$j})) {
                            $i = $j-1;
                            break;
                        }
                    }
                }
                
                $split_path[] = substr($class, $offset, $i-$offset);
                $offset = $i;
            }
        }
  
        $class_path = implode('/', $split_path) . '/' .
                      substr($class, $offset) . '.php';
        
        include_once self::$baseDir . $class_path;
    }

    public static function autoload($class) {
        self::loadClass($class);
    }
    
    public static function registerAutoload($class='ExLoader') {
        spl_autoload_register(array($class, 'autoload'));
    }
}