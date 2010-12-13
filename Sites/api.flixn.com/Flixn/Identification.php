<?php
/**
 * Flixn
 *
 * @category    Flixn
 * @package     Base
 * 
 * @author      Samuel J. Greear
 * @copyright   Copyright (c) 2008 Flixn, Inc.
 * @version     $Id $
 */

class FlixnIdentification {

    const UUID_FORMAT = '%8.8s-%4.4s-%4.4s-%4.4s-%12.12s';

    public function __construct() {
    }

    public function UUIDGenerate($ident) {
        if (!is_int($ident))
            throw new Exception();
            
        if ($ident < 0 || $ident > 9999)
            throw new Exception();

        $time = explode('.', microtime(true));
        
        $hash = sha1($_SERVER['REMOTE_ADDR'] .
                     $_SERVER['REMOTE_PORT'] .
                     $_SERVER['REQUEST_TIME'] .
                     $_SERVER['SERVER_ADDR']);
                     
        return sprintf(self::UUID_FORMAT,
                       substr('00000000' . dechex($time[0]), -8),
                       substr('0000' . dechex($time[1]), -4),
                       substr('0000' . $ident, -4),
                       substr($hash, 0, 4), substr($hash, 4, 12));
    }

    public function UUIDKeyGenerate() {
        $hash = sha1($_SERVER['REMOTE_ADDR'] .
                     $_SERVER['REMOTE_PORT'] .
                     $_SERVER['SERVER_ADDR'] .
                     microtime(true));
                     
        return sprintf(self::UUID_FORMAT,
                       substr($hash, 0, 8),
                       substr($hash, 8, 12),
                       substr($hash, 12, 16),
                       substr($hash, 16, 20),
                       substr($hash, 20, 32));
    }
    
    public function UUIDSessionGenerate($ident) {
        $hash = sha1($_SERVER['REMOTE_ADDR'] .
                     $_SERVER['REMOTE_PORT'] .
                     $_SERVER['SERVER_ADDR'] .
                     microtime(true) .
                     mt_rand());
                     
        return sprintf(self::UUID_FORMAT,
                       substr($hash, 0, 8),
                       substr($hash, 8, 12),
                       substr('0000' . $ident, -4),
                       substr($hash, 12, 16),
                       substr($hash, 16, 28));
    }

    public function UUIDExtractIdent($uuid) {
        $suuid = explode('-', $uuid);
        return $suuid[2];
    }
    
    public function toBase36($num) {
        if (!is_numeric($num))
            throw new Exception();

        return base_convert($num, 10, 36);
    }

    public function fromBase36($str) {
        if (!is_string($str))
            throw new Exception();

        return base_convert($str, 36, 10);
    }
}