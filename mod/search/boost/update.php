<?php
/**
 * update file for search
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

function search_update(&$content, $currentVersion)
{
    switch ($currentVersion) {
        case version_compare($currentVersion, '0.2.0', '<'):
            $files[] = 'conf/en_us_wordlist.txt';
            $files[] = 'conf/wordlist.txt';
            $result = PHPWS_Boost::updateFiles($files, 'search');
            if ($result) {
                if (Core\Error::isError($result)) {
                    Core\Error::log($result);
                    $content[] = 'Unable to copy wordlist files locally.';
                } else {
                    $content[] = 'Wordlist files updated.';
                }
            }  else {
                $content[] = 'Wordlist files updated.';
            }
            $content[] = '<pre>
0.2.0 Changes
-------------
+ Moved deletion of keyword to its own function
+ Fixed javascript confirmation on item deletion
+ Deletion of keyword is now htmlentitified to catch foreign
  characters
+ Added resetKeywords function
+ FilterWords now has a htmlentities parameter and allows foreign
  characters (bug #1602039). Thanks WeBToR
+ Config file is no longer hardcoded. Now picks file based on language.
+ If all search words are undersized, search will not throw an
  error anymore.
</pre>';

        case version_compare($currentVersion, '0.2.1', '<'):
            $content[] = '<pre>
0.2.1 Changes
-------------
+ Added translate functions.
</pre>';

        case version_compare($currentVersion, '0.2.2', '<'):
            $content[] = '<pre>
0.2.2 Changes
-------------
+ Updated translation functions.
+ Added German translation files.
</pre>';        

        case version_compare($currentVersion, '0.2.3', '<'):
            $content[] = '<pre>';
            searchUpdateFiles(array('templates/search_page.tpl'), $content);
            $content[] = '0.2.3 Changes
-------------
+ Changed h1 headers to h2
</pre>';        

        case version_compare($currentVersion, '0.3.0', '<'):
            $content[] = '<pre>';
            searchUpdateFiles(array('templates/search_box.tpl', 'templates/settings.tpl'), $content);
            $content[] = '0.3.0 changes
-------------
+ Added ability to put search radio buttons on search bar.
</pre>';        

        case version_compare($currentVersion, '0.3.1', '<'):
            $content[] = '<pre>';
            $content[] = '0.3.1 changes
-------------
+ Foreign chanacters properly checked
+ Moved a character check higher to prevent possible XSS
  (Thanks Audun Larsen).
</pre>';        

        case version_compare($currentVersion, '0.3.2', '<'):
            $content[] = '<pre>0.3.2 changes
-------------
+ Changed REQUEST references to GET.
+ More parsing of words added.
+ Search modules available shows only modules currently containing data.</pre>';        

        case version_compare($currentVersion, '0.4.0', '<'):
            $content[] = '<pre>0.4.0 changes
-------------
+ Added a group by to prevent errors in postgresql.
+ Fixed: wrong class name to key registration
+ PHP 5 formatted.
</pre>';        

            case version_compare($currentVersion, '0.4.1', '<'):
            $content[] = '<pre>0.4.1 changes
-------------
+ PHP 5 strict formatted.</pre>';        
    }

    return TRUE;
}

function searchUpdateFiles($files, &$content)
{
    if (PHPWS_Boost::updateFiles($files, 'search')) {
        $content[] = '--- Updated the following files:';
    } else {
        $content[] = '--- Unable to update the following files:';
    }
    $content[] = "    " . implode("\n    ", $files);
    $content[] = '';
}


?>