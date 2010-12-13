<?php

/*
<directory>
  <descriptor>
    <type>directory</type>
    <name>.</name>
  </descriptor>
  <descriptor>
    <type>directory</type>
    <name>..</name>
  </descriptor>
  <descriptor>
    <type>file</type>
    <name>foo.txt</name>
    <size>2.1Kb</size>
    <modified>
  </descriptor>
</directory>
*/

class Dir extends XMLement {

    var $Restricted;

    var $Dir = NULL;
    var $DirHandle = NULL;

    var $Error = '';

    var $TimeFormat = "d-F-Y H:i:s";

    function __construct($dir=NULL) {
        parent::__construct('directory', NULL, 'directory');

        $this->Restricted = array();
        $this->Restricted[] = '.';
        $this->Restricted[] = '.svn';
        $this->Restricted[] = 'CVS';
        $this->Restricted[] = '.htaccess';

        global $_APPREQ;

          // check for trailing slashes
        if (substr($dir, -1, 1) != '/') {
          $dir .= '/';
          $_APPREQ['uri'] .= '/';
        }

        if ($dir != NULL) {
            $this->OpenDir($dir);
            $this->ReadDir();
        }

        print stristr($_APPREQ['page'], $_APPREQ['uri']);
        $this->AddElement('fspath', ereg_replace(SITE_BASE, "", $dir));
        $this->AddElement('path', $_APPREQ['uri']);
    }

    function OpenDir($dir) {
        if (!is_dir($dir)) {
            $this->Error = "$dir is not a directory";
            throw new Exception($this->Error);
        }

        $this->DirHandle = opendir($dir);
        if (!$this->DirHandle) {
            $this->Error = "Could not open directory: $dir";
            throw new Exception($this->Error);
        }

        $this->Dir = $dir;
    }

    function ReadDir() {
        if (!$this->DirHandle) {
            $this->Error = "Tried to 'ReadDir' without successfully " .
                           "opening a directory via 'OpenDir'";
            throw new Exception($this->Error);
        }

        while (($filename = readdir($this->DirHandle)) !== false) {
            if (!in_array($filename, $this->Restricted)) {
                $node = $this->ForkChild('descriptor');

                $tmpfname = $this->Dir . '/' . $filename;

                $node->AddElement('type', (is_dir($tmpfname)) ? 'directory' : 'file');

                $node->AddElement('name', $filename);
                $node->AddElement('size', $this->FileSize($tmpfname));

                $node->AddElement('modified',
                    date($this->TimeFormat, filemtime($tmpfname)));

                $node->AddElement('permissions', $this->FilePerms($tmpfname));
                $this->AddChild($node);
            }
        }

    }

    function FileSize($file) {
        $a = array("B", "Kb", "Mb", "Gb", "Tb", "Pb");

        $pos = 0;
        $size = filesize($file);
        while ($size >= 1024) {
          $size /= 1024;
          $pos++;
        }

        return round($size,2)." ".$a[$pos];
    }

    function SetTimeFormat($format) {
      $this->TimeFormat = $format;
    }

    function FilePerms($file) {
        $perms = fileperms($file);

        if (($perms & 0xC000) == 0xC000) {
              // Socket
            $info = 's';
        } elseif (($perms & 0xA000) == 0xA000) {
              // Symbolic Link
            $info = 'l';
        } elseif (($perms & 0x8000) == 0x8000) {
              // Regular
            $info = '-';
        } elseif (($perms & 0x6000) == 0x6000) {
              // Block special
            $info = 'b';
        } elseif (($perms & 0x4000) == 0x4000) {
              // Directory
            $info = 'd';
        } elseif (($perms & 0x2000) == 0x2000) {
              // Character special
            $info = 'c';
        } elseif (($perms & 0x1000) == 0x1000) {
              // FIFO pipe
            $info = 'p';
        } else {
              // Unknown
            $info = 'u';
        }

          // Owner
        $info .= (($perms & 0x0100) ? 'r' : '-');
        $info .= (($perms & 0x0080) ? 'w' : '-');
        $info .= (($perms & 0x0040) ?
                 (($perms & 0x0800) ? 's' : 'x' ) :
                 (($perms & 0x0800) ? 'S' : '-'));

          // Group
        $info .= (($perms & 0x0020) ? 'r' : '-');
        $info .= (($perms & 0x0010) ? 'w' : '-');
        $info .= (($perms & 0x0008) ?
                 (($perms & 0x0400) ? 's' : 'x' ) :
                 (($perms & 0x0400) ? 'S' : '-'));

          // World
        $info .= (($perms & 0x0004) ? 'r' : '-');
        $info .= (($perms & 0x0002) ? 'w' : '-');
        $info .= (($perms & 0x0001) ?
                 (($perms & 0x0200) ? 't' : 'x' ) :
                 (($perms & 0x0200) ? 'T' : '-'));

        return $info;
    }
}

?>
