<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

if (!isset($_SESSION['Breadcrumb'])) {
    $_SESSION['Breadcrumb'] = new Breadcrumb;
}

$_SESSION['Breadcrumb']->display();

?>