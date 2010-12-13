<?php
if (!isset($_REQUEST['cid']) && !isset($_REQUEST['mediaid']))
    exit();
?>
<html>
  <head>
    <script type="text/javascript" src="http://www.flixn.com/js/swfobject2.js"></script>
    <script type="text/javascript">
      swfobject.embedSWF('http://components.flixn.evilprojects.net/<?= $_REQUEST['cid'] ?>.swf',
                         'flixn_component_instance', '100%', '100%', '9.0.115',
                         'http://components.flixn.evilprojects.net/expressinstall.swf',
                         { mediaId: '<?= $_REQUEST['mediaid'] ?>'},
                         { allowscriptaccess: 'always', allownetworking: 'all',
                           menu: 'false', allowfullscreen: 'true', },
                         {});
    </script>
    <style type="text/css">
      html { margin: 0; padding: 0; }
      body { margin: 0; padding: 0; }
    </style>
  </head>
  <body>
    <div id="flixn_component">
      <div id="flixn_component_instance"></div>
    </div>
  </body>
</html>
