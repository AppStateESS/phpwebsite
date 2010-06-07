<?php

/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at appstate dot edu>
 */

if (!isset($_SESSION['Whodis'])) {
    Core\Core::initModClass('whodis', 'Whodis.php');
    Whodis::record();
    $_SESSION['Whodis'] = true;
}

?>