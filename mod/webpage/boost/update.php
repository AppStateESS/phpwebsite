<?php
  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function webpage_update(&$content, $currentVersion)
{

    switch ($currentVersion) {

    case version_compare($currentVersion, '0.5.2', '<'):
        $content[] = '<pre>Web Page versions prior to 0.5.2 are not supported for updating.
Please download version 0.5.3</pre>';
        break;

    case version_compare($currentVersion, '0.5.3', '<'):
        $content[] = '<pre>';
        if (PHPWS_Boost::updateFiles(array('conf/error.php'), 'webpage')) {
            $content[] = '--- Updated conf/error.php';
        } else {
            $content[] = '--- Unable to update conf/error.php';
        }
        $content[] = '
0.5.3 Changes
--------------
+ Added error catch to page template function.
</pre>';

    case version_compare($currentVersion, '0.5.4', '<'):
        $content[] = '<pre>
0.5.4 Changes
--------------
+ Fulfilled request to change "edit" to "edit page"
+ RFE #1690681 - Added permissions link on volume list view.
+ RFE #1719299 - Page titles added to volume tabs.
</pre>';

    case version_compare($currentVersion, '0.5.5', '<'):
        $content[] = '<pre>';
        
        $files = array('templates/page/basic.tpl', 'templates/page/prev_next.tpl',
                       'templates/page/short_links.tpl', 'templates/page/verbose_links.tpl');
        webpageUpdateFiles($files, $content);
        

        $content[] = '0.5.5 Changes
--------------
+ Fixed Bug #1784432. Missing underline caused missing class
  error.
+ Put a ten character limit on the page title appearing in the tab
+ Wrapped each page template with webpage-page classed div.
</pre>';

    case version_compare($currentVersion, '1.0.0', '<'):
        $content[] = '<pre>';
        $files = array('templates/forms/list.tpl', 'templates/header.tpl');
        webpageUpdateFiles($files, $content);
      
        if (!PHPWS_Boost::inBranch()) {
            $content[] = file_get_contents(PHPWS_SOURCE_DIR . 'mod/webpage/boost/changes/1_0_0.txt');
        }
        $content[] = '</pre>';

    case version_compare($currentVersion, '1.0.1', '<'):
        $content[] = '<pre>';
        $files = array('templates/forms/list.tpl');
        webpageUpdateFiles($files, $content);
      
        if (!PHPWS_Boost::inBranch()) {
            $content[] = file_get_contents(PHPWS_SOURCE_DIR . 'mod/webpage/boost/changes/1_0_1.txt');
        }
        $content[] = '</pre>';

    case version_compare($currentVersion, '1.1.0', '<'):
        $content[] = '<pre>';
        PHPWS_Core::initModClass('filecabinet', 'Cabinet.php');
        if (Cabinet::convertImagesToFileAssoc('webpage_page', 'image_id')) {
            $content[] = '--- Converted images to new File Cabinet format.';
        } else {
            $content[] = '--- Could not convert images to new File Cabinet format.</pre>';
            return false;
        }
        $files = array('templates/page/basic.tpl', 'templates/page/prev_next.tpl',
                       'templates/page/short_links.tpl', 'templates/page/verbose_links.tpl',
                       'templates/style.css');

        webpageUpdateFiles($files, $content);
        $content[] = '1.1.0 changes
---------------
+ Works with new mod_rewrite.
+ Updated to work with File Cabinet 2.0
+ Styled the admin links</pre>';

    case version_compare($currentVersion, '1.1.1', '<'):
        $content[] = '<pre>1.1.1 changes
---------------
+ Added missing page option to plugForward function.
</pre>';

    }

    return TRUE;
}

function webpageUpdateFiles($files, &$content)
{
    if (PHPWS_Boost::updateFiles($files, 'webpage')) {
        $content[] = '--- Successfully updated the following files:';
    } else {
        $content[] = '--- Was unable to copy the following files:';
    }
    $content[] = '     ' . implode("\n     ", $files);
    $content[] = '';
}

?>