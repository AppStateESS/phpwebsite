<?php
$link[] = array ('label'    => dgettext('phpwsbb', 'phpWs Bulletin Board'),
         'restricted'       => FALSE,
         'url'              => 'index.php?module=phpwsbb',
         'description'      => 'A bulletin board module.',
         'image'            => 'phpwsbb.png',
         'tab'              => 'content');

$link[] = array ('label'    => dgettext('phpwsbb', 'phpWs Bulletin Board'),
         'restricted'       => TRUE,
         'url'              => 'index.php?module=phpwsbb&op=config',
         'description'      => 'A bulletin board module.',
         'image'            => 'phpwsbb.png',
         'tab'              => 'admin');

?>