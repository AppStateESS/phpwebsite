<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

if (!isset($_SESSION['Clipboard'])) {
    return;
}

Clipboard::show();

?>