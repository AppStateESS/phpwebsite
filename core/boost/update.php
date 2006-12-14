<?php

  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

function core_update(&$content, $version) {
    $content[] = '';
    switch ($version) {

    case version_compare($version, '1.0.5', '<'):
        $content[] = '- Fixed core version on installation.';
        $content[] = '- Changed Core.php and Module.php to track core\'s version better. Helps Boost with dependencies';

    case version_compare($version, '1.0.6', '<'):
        $content[] = '- Fixed locale cookie saving incorrectly.';

    case version_compare($version, '1.0.7', '<'):
        $content[] = '- Key.php : Added parameter to avoid home keys when calling getCurrent.';
        $content[] = '- Database.php : fixed a small bug with adding columns using "as". Value was carrying over to other columns.';
        $content[] = '- Form.php : Added an error check on a select value.';
        $content[] = '- Documentation : updated DB_Pager.txt with information on setting a column order.';
        $content[] = '- Init.php - Commented out putenv functions.';
        $content[] = '- Javascript : close_refresh - added option to not auto-close';

    case version_compare($version, '1.0.8', '<'):
        $content[] = '- Module.php : now adds error to _error variable if module could not be loaded.';

    case version_compare($version, '1.0.9', '<'):
        $content[] = '- Form.php : fixed crutch function for radio buttons and check boxes.';

    case version_compare($version, '1.1.0', '<'):
        $content[] = 'Fix - Added a define for CURRENT_LANGUAGE if gettext is not working.';
        $content[] = 'Fix - Altered the count type for select slightly.';
        $content[] = 'Fix - PHPWS_File\'s copy_directory function was reporting the wrong value in its error messages.';
        $content[] = 'Fix - In Settings, added an error check to prevent null values from being saved in the integer columns.';
        
        $content[] = 'New - Reworked Database class to allow table prefixing and concurrent connections.';
        $content[] = 'New - Added table prefixing back to install process in Setup.';
        $content[] = 'New - DB factory files have been broken out into specially named classes, hopefully this will allow dual connections on different database systems.';
        $content[] = 'New - Removed Crutch_Db.php.';
        $content[] = 'New - Null values are not considered recursive values in the Debug test function.';
        $content[] = 'New - In Convert, added a table check to getSourceDB function. Calendar updated.';
        $content[] = 'New - In Settings, added a reset function that sets a value back to the default.';
        $content[] = 'New - Error checks added to Batch.';
        $content[] = 'New - Removed the static tables variable in Database\'s isTable function. Possibility exists that two or more databases could be used and the static list would return faulty information.';

    case version_compare($version, '1.1.1', '<'):
        $content[] = 'Fix - Blog conversion now copies summary and body correctly.';
        $content[] = 'Fix - File Cabinet conversion checks for Documents module before beginning.';
        $content[] = 'Fix - Users conversion now sets users as active and approved.';
        $content[] = 'Fix - Settings reloads after saving values. Prevent bad data.';

    case version_compare($version, '1.1.2', '<'):
        $content[] = 'Fix - Block conversion now places all blocks on front page.';
        $content[] = 'Fix - Bug #1588765 : addOrder\'s random option works again.';

    case version_compare($version, '1.1.3', '<'):
        $content[] = 'New - Added the "condense" function to Text class.';
        $content[] = 'New - Key now uses the condense function for the summary.';
        $content[] = 'Fix - Setting now resets only the module\'s values after saving.';

    case version_compare($version, '1.2.0', '<'):
        $content[] = 'Fix - Core.php : duplicate slashes on home urls removed.';
        $content[] = 'Fix - Convert : Users in Postgresql conversions should now work. User names are stored in lowercase by default.';
        $content[] = 'Fix - Database : LIKE comparison now is ALWAYS case insensitive. This standard allows different database OSs to operate identically in the software.';
        $content[] = 'Fix - Database : fixed some table identification errors which fouled up table prefixing.';
        $content[] = 'Fix - Text : htmlentities in parseInput shouldn\'t mangle foreign characters anymore.';
        $content[] = 'Fix - Init : two statements\' orders were flipped by mistake.';
        $content[] = 'New - Convert : Web Page conversion allows you to choose whether you want all the sections in one page or separate pages.';

        $content[] = 'New - Database : added some sanity checks to normalize queries.';
        $content[] = 'New - PHPWS_Stats : added a display_error ini_set. It is commented out.';
        $content[] = 'New - Text : added a parameter to parseOutput to control display of smilies.';
        $content[] = 'New - Text : makeRelative has a parameter to determine the local directory prefix.';

    case version_compare($version, '1.2.1', '<'):
        $content[] = 'Fix - Text : another small change to get foreign characters to work in php < 5.';

    case version_compare($version, '1.3.0', '<'):
        $content[] = '<pre>
1.3.0 Changes
-------------
+ Init.php - Added UTF-8 to setlocale language string.
+ Added photoalbum conversion
+ Convert
  o Added ability to set table prefix of conversion database.
  o Added Phatform category conversion.
  o Added Phatform conversion script.
  o Added note about moving  your images and files before or after
    conversion to readme file.
  o Now looks for info.ini file if language version not available
  o Added info.ini files for each converted module
  o Removed unused en-us ini files.
+ Database
  o Added a report_error parameter to importFile function.
  o Fixed export function.
+ Error.php - Default language is now used when the get function is called.
+ Init.php
  o Default language changed from "en" to "en_US"
  o Stripping the utf-8 suffix from the language used in
    translateFile.
+ Form.php - Change to the editor code that should prevent the
  doubling of breaks
+ Text.php
  o added the smilies parameter to the constructor.
  o Added a function to fix basic anchors in parsed text. Will now
    suppliment them with the current url.
  o Added a parameter to control whether to fix anchors by default or
    not. Default set in config/core/text_settings.php update.

+ Captcha.php - New class to help with CAPTCHA
+ Setup - added captcha information to config.tpl, removed file_type include
+ Index.php - Added file_types include
+ Added nonpareil theme
+ Functions.php - Added str_split
</pre>';

    case version_compare($version, '1.3.1', '<'):
        $content[] = '<pre>
1.3.1 Changes
-------------
+ Database.php
  o Added a DB_ALLOW_TABLE_INDEX definition to allow users with limited
    access to install.
  o Changed Database to use factory classes for adding table
    columns. Good ole postgresql!
  o Added index define to setup config template
+ Text.php
  o Added a couple of lines to breaker to prevent breaks inside pre
    tags
</pre>';

    case version_compare($version, '1.3.2', '<'):
        $content[] = '<pre>
1.3.2 Changes
-------------
+ Database.php
  o Added code to prevent index creation on sql imports if requested.
+ Setup
  o Removed RC stuff from welcome.
  o Changed setup colors.
</pre>';

    case version_compare($version, '1.3.3', '<'):
        $content[] = '<pre>
1.3.3 Changes
-------------
+ Cache.php
  o Added site hash to key.
+ Text.php
  o Changed mb_convert_encode to utf8_encode.
+ pear.tgz
  o Removed some unused and repeated classes
</pre>';

    case version_compare($version, '1.3.4', '<'):
        $files = array();
        $files[] = 'conf/error.php';
        $files[] = 'javascript/check_all/head.js';

        $result = PHPWS_Boost::updateFiles($files, 'core');

        if (PEAR::isError($result)) {
            return $result;
        }

        if ($result) {
            $content[] = 'Core file upgrade successful.';
        } else {
            $content[] = 'Failed to upgrade core files.';
            return false;
        }
        $content[] = '<pre>
1.3.4 Changes
-------------
+ General
  o Moved core config file to new conf directory
  o Tarred pear directory so would uncompress in lib directory

+ Setup
  o Allows you to install phpWebSite into an existing database
  o Set default editor to FCK

+ Module.php
  o Can now initialize core as a module

+ Core.php
  o Removed loadAsMod function. Ability transfered to Module class.

+ DBPager
  o Added saveLastView and getLastView functions. Allows the dev to
    record where the user was last if they may leave the page and need
    to return where they left.  
  o Replaced regexp expression with like expression. regexp
    incompatible with postgresql. 

+ Text.php
  o Removed function that cleaned up word quotes. (Bug #1602046)
  o Changed isValidInput to allow emails with slashes (Bug #1600198)
  o Altered isValidInput url check slightly
  o changed double quotes to singles in isValidInput

+ Updated files : 
  o moved conf/errorDefines.php to new inc/ directory.
  o config/conf/error.php - added new key error message
  o javascript/check_all/head.js - check all was not functioning
                                   correctly 

+ Documentation - added note about resetKeywords in Search module.

+ Database.php - added an error check on the where creation
+ Init.php - errorDefines.php now pulled from the source inc directory
+ Key.php - Added an error check to restrictView function

+ Convert
  o Added logout link.
  o Added error check to phatform conversion.

+ Cookie.php
  o Added error check to unset function (bug #1599140).
</pre>';


    case version_compare($version, '1.3.5', '<'):
        $content[] = '<pre>
1.3.5 Changes
--------------
+ Conversion - added note to readme
+ Core.php - Fixed bug with getHomeHttp function
+ Javascript
  o Rewrote portions of FCKeditor file manager. Works better now.
  o Rewrote FCKeditor upload to work better with links.
</pre>';


    case version_compare($version, '1.3.6', '<'):
        $content[] = '<pre>
1.3.6 Changes
--------------
+ Pear - added mime file for email class
+ Convert
  o Convert class now allows the user to pick a destination branch.
  o siteDB function added. Used in developer\'s convert.php file to
    restore the branch connection after a disconnect.
  o Fixed File Cabinet conversion typo (bug #1608912).
+ Documentation
  o Settings_Class - Fixed directory for settings file
+ Settings
  o Set password character limits
  o Moved error messages to appropriate areas
  o Now allows null settings.
+ Javascript
  o Added suggested js_calendar fix. Thanks Verdon.
</pre>';


   case version_compare($version, '1.3.7', '<'):
       if (PHPWS_Boost::updateFiles(array('conf/text_settings.php'), 'core')) {
           $content[] = '+ Configuration file text_settings.php updated.';
       } else {
           $content[] = '+ Configuration file text_settings.php could not be updated.';
       }
        $content[] = '<pre>
1.3.7 Changes
--------------
+ Editor
  o Added a "limited" option to allow the wysiwyg stub developer to
    have a second set of tools.

+ Form
  o useEditor function will now accept a limited parameter.

+ Text
  o Added parameter to makeRelative to force a change only inside link
    tags
  o makeRelative is run before htmlentities is called.
  o makeRelative only changes urls next to the characters =". This
    should prevent displayed addresses from being relativized.
  o Added function to collapse long urls.

+ Documentation
  o Added a small note to DB_Pager.txt to inform the user that basic
    instruction does not end at the $pager->get() function.
  o Editor.txt - Added instructions on using the Editor class with the
    Form class. 
  o Settings_Class.txt - fixed the path location for the settings.php
    file. 

+ Javascript
  o FCKEditor - added ability to show limited wysiwyg tool

+ Setup
  o Error messages now appear under incorrect setting

+ DBPager
  o Current page set back to page 1 after a new search or if current
    page is set to zero
  o setOrder function now has a default of \'asc\' (ascending).
  o Fixed instant where searches were joined with AND instead of OR
  o Pager search variable is reset when a new search is
    called. Prevents empty variable in GET string.
  o Pager now grabs navigation and sort buttons regardless of row
    status. 
</pre>';
 
    }
    
    return true;
}


?>