<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id: install.php 20 2006-10-18 18:23:18Z matt $
   */

function phatform_install(&$content)
{
    if (!@mkdir('files/phatform/')) {
        $content[] = 'Failed to create files directory.';
    }

    if (!@mkdir('files/phatform/archive/')) {
        $content[] = 'Failed to create archive directory.';
    }

    if (!@mkdir('files/phatform/report/')) {
        $content[] = 'Failed to create report directory.';
    }

    if (!@mkdir('files/phatform/export/')) {
        $content[] = 'Failed to create report directory.';
    }

    return true;
}

?>