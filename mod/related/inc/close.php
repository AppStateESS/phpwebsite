<?php
/**
 * @version $Id$
 */

if (isset($_SESSION['Related_Bank'])) {
    $_SESSION['Related_Bank']->show();
} else {
    $related = new Related;
    $related->show();
}

?>