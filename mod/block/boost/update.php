<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */


function block_update(&$content, $currentVersion)
{
    switch ($currentVersion) {
    case version_compare($currentVersion, '1.1.1', '<'):
        $content[] = '<pre>Block versions prior to 1.1.1 are not supported for updating.
Please download version 1.1.2.</pre>';
        break;

    case version_compare($currentVersion, '1.1.2', '<'):
        PHPWS_Boost::updateFiles(array('img/block.png'), 'block');
        $content[] = '<pre>1.1.2 changes
-------------
+ Added German files
+ Use new translation format
+ Changed control panel icon
</pre>';

    case version_compare($currentVersion, '1.1.3', '<'):
        $content[] = '<pre>';
        if (PHPWS_Boost::updateFiles(array('templates/sample.tpl'), 'block')) {
            $content[] = '--- Successfully copied templates/sample.tpl';
        } else {
            $content[] = '--- Unable to copy templates/sample.tpl';
        }
        $content[] = '
1.1.3 changes
-------------
+ Changed the sample.tpl layout to conform with other "box" templates.
</pre>';
    }
    
    return TRUE;
}

?>