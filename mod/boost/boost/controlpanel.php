<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */
$link[] = array('label' => dgettext('boost', 'Boost'),
    'restricted' => TRUE,
    'url' => 'index.php?module=boost&amp;action=admin',
    'description' => dgettext('boost',
            'Boost allows the installation and upgrading of modules.'),
    'image' => 'boost.png',
    'tab' => 'admin'
);
?>