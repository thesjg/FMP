<?php

class IdentifyMedia {
    
    /* XXX */
    private $identCmd = '/z/www/upload.flixn.evilprojects.net/mediainfo/mediainfo.sh';
    private $output   = NULL;
    
    private $mediaType = NULL;
    private $mediaInfo = array();
    
    public function __construct($fileName) {
        $command = "$this->identCmd -f $fileName";
        exec($command, $output, $ret);

        if ($ret !== 0) { /* throw hissy fit */ }
        
        $this->output = $output;
        $this->parseOutput();
    }
    
    public function getMediaType() {
        return $this->mediaType;
    }
    
    public function getMediaInfo() {
        return $this->mediaInfo;
    }
    
    private function parseOutput() {
        
        // First element is going to be our media type
        $this->mediaType = array_shift($this->output);
        
        foreach ($this->output as $line) {
            list($key, $value) = explode(':', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            $this->mediaInfo[$key] = $value;
        }
    }
}

/*
class IdentifyMedia {

    private $mediainfoCmd = '/usr/local/bin/mediainfo';
    private $output       = NULL;

    public function __construct($fileName) {

        $command = "$this->mediainfoCmd $fileName";
        exec($command, $output);

        $this->output = $output;
    }

    public function getVideoCodec() {
        $block = 'Video #0';
        $tag = 'Codec';

        $codec = NULL;

        $in_block = false;
        foreach ($this->output as $line) {
            if (strncmp($block, $line, strlen($block)) == 0) {
                $in_block = true;
                continue;
            }

            if ($in_block) {
                list($key, $value) = explode(':', $line);
                $key = trim($key);
                $value = trim($value);

                if ($key == $tag) {
                    $codec = $value;
                    break;
                }
            }
        }

        return $codec;
    }
}
*/