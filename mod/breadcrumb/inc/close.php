<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id: close.php 7340 2010-03-15 19:55:46Z matt $
 */

if (!isset($_SESSION['Breadcrumb'])) {
    $_SESSION['Breadcrumb'] = new Breadcrumb;
}

$_SESSION['Breadcrumb']->display();

?>