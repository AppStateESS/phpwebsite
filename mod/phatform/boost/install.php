<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function phatform_install(&$content)
{
    if (!@mkdir('files/phatform/')) {
        $content[] = _('Failed to create files directory.');
    }

    if (!@mkdir('files/phatform/archive/')) {
        $content[] = _('Failed to create archive directory.');
    }

    if (!@mkdir('files/phatform/report/')) {
        $content[] = _('Failed to create report directory.');
    }

    if (!@mkdir('files/phatform/export/')) {
        $content[] = _('Failed to create export directory.');
    }

    return true;
}

?>