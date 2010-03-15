<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

$default['timeout'] = 0;
$default['refresh'] = 1;
$default['set_timeout'] = ' ';

if (isset($data['use_link'])) {
    unset($default['set_timeout']);
}

?>