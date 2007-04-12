<?php

  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

function core_update(&$content, $version) {
    $boost_module = new PHPWS_Module('boost');
    if (version_compare($boost_module->version, '2.0.0', '<')) {
        $content[] = '<h1>Important!</h1>
<p>Core cannot properly update your installation yet. You will need to grab a recent copy of Boost (1.9.8 or higher).</p>
<p>Do not try to update Boost, just copy it into the mod/boost/ directory. Once you have done that, update the Core, then update Boost.</p>
';
        return false;
    }

    $content[] = '';
    switch ($version) {

        // Removed older versions 15 Feb 2007
    case version_compare($version, '1.3.3', '<'):
        $content[] = 'Sorry, but versions under 1.3.3 require the 1.3.7 update.';
        return false;


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
        
    case version_compare($version, '1.4.0', '<'):
        $content[] = '<pre>';
       if (PHPWS_Boost::updateFiles(array('conf/language.php'), 'core')) {
           $content[] = 'Configuration file language.php created.';
       } else {
           $content[] = 'Configuration file language.php could not be created.';
       }
        $content[] = '
1.4.0 Changes
--------------
Core classes
------------
+ Database class
  o addTableColumn checks for a column\'s existence and returning false if so.
    Prevents error lock ups.
  o Fixed getObjects. Extra parameters were passed to constructors BUT
    the object is not passed by reference in php 4. Once the object
    was created, it was cleared.
  o Fixed some mixups in the setLimit function
  o Added limit error check to mysql.php and mysqli.php.
  o Limit variables passed by reference instead of copied
  o Added Hilmar\'s prefixing code to prevent table prefixing inside single
    quotes. 

+ Module
  o Default image and file directory setting is "false".

+ Settings class
  o Bug 1659055 - if a single setting is saved without loading the
    module, all settings would get reset. Made change to only clear
    items getting saved.
  o Now changes numeric strings to integers

+ Init script
  o Changed Init.php translate function. Should now track previous
    directory setting properly.
  o Removed textdomain function from Init.php. It was a redundant call.
  o Strips the UTF8 off the end of locale to match preference file

+ File class
  o Rewrote readDirectory function.
  o Fixed bug in rmdir function preventing files inside directories
    from being deleted. 

+ Core class
  o Added check for leading slash in url sent to reroute function
  o Added setLastPost to reroute function. Function was getting skipped.
  o Changed Branch checking method (Bug #1638042)
  o Added error checks to goBack to prevent endless loop
  o Added bookmark and returnToBookmark functions

+ DBPager 
  o Part of setLimit problem was pager was working around the error by
    sending backward data. Fixed.
  o Fixed pagination if the limit was raised on a high page count
  o Removing resetColumns function from getTotalRows function.
  o Error check added before calling javascriptEnabled function.

Javascript
----------
+ Editors
  o Fixed FCKeditor\'s style sheet
  o Removed dependence on SCRIPT_FILENAME variable for FCKeditor\'s file
    manager
  o Fixed the style settings in the FCKeditor
  o Added style settings note to FCKeditor readme file
  o Converted current 0.10.x wysiwyg to editor named "simple"


Default theme
---------------
+ Changed padded style from px to em
+ Added new form style for dropping out of form tables.
+ Added code to prevent image overlap in blog and webpage
+ Added margin to bottom of blog
+ Added theme.php file.


Language
--------
+ Added several translate functions throughout the base classes.
+ Moved language settings to a new config file : language.php 
+ Updated po file
+ Removed language setting from setup config template
+ Changing xgettext method for creating message files. No longer
  listing translation line and file numbers. Old method caused
  complex diffs. Also sorting the source alphabetically.

Documentation
-------------
+ Theme_Creation.txt - added information on locking variables and
  using the theme.php file.
+ Created Captcha.txt documentation for Captcha class.
+ Language.txt - Changed xgettext method.


Conversion
----------
+ Error checked added to Blog to assure Announcements was installed.


Updated files:
conf/language.php
';

    case version_compare($version, '1.4.1', '<'):
        $content[] = '<pre>';
        if (PHPWS_Boost::updateFiles(array('conf/i18n/en_US.php', 'conf/text_settings.php'), 'core')) {
           $content[] = 'Added config/core/i18n/en_US.php file and updated config/core/text_settings.php.';
       } else {
           $content[] = 'Unable to add config/core/i18n/en_US.php or update config/core/text_settings.php file.';
       }
        $content[] = '
1.4.1 Changes
--------------
+ Removed constructor references from Template.php
+ phpws_info can now list browser info.
+ Editor
  o Removed htmlarea directory
  o Added supported.php files to FCK and tinymce (simple doesn\'t need
    it)
  o Editor will now check the user cookie for an Editor choice. It
    defaults to the core config.php setting.
  o Set support settings for safari in tinymce

+ Translate
  o fixed i18n files. en_EN.php changed to en_US.php

+ Function
  o Added a parse check on a PHP 4 function name for Compat mode.

+ DB_Pager
  o Cleaned up translate functions.
  o Added alt and title parameters to sorting images for xhtml
    compliance.

+ Documentation - fixed typo in DB_Pager.txt
';

    case version_compare($version, '1.4.2', '<'):
        $content[] = '<pre>
1.4.2 Changes
--------------
+ Fixed shorten url function in Text.php
</pre>';

    case version_compare($version, '1.4.3', '<'):
        $content[] = '<pre>1.4.3 Changes
--------------';
        $files = array('conf/language.php',
                       'javascript/editors/FCKeditor/supported.php',
                       'javascript/editors/tinymce/supported.php',
                       'javascript/editors/FCKeditor/editor/phpwsstyles.xml');
        if (PHPWS_Boost::updateFiles($files, 'core')) {
            $content[] = '+ Copied the following files:';
        } else {
            $content[] = '+ Unable to copy the following files:';
        }
        $content[] = '    ' . implode("\n    ", $files);

        if (is_dir('javascript/editors/htmlarea/')) {
            if (@rmdir('javascript/editors/htmlarea/')) {
                $content[] = '+ Deleted htmlarea editor directory.';
            } else {
                $content[] = '+ Tried to delete htmlarea editor directory but failed.';
            }
        }
        $content[] = '+ Fixed issue with selecting columns with setindex in Database class.
+ Web Page conversion adds missing create_user_id.
+ Removed Windows version types from browser indentification.
+ Browser identification broadened.
+ Database column select bug fixed.
+ Removed fake French translation (as should you!!!)';

    case version_compare($version, '1.5.0', '<'):
        // not finished
        @mkdir('templates/cache/');
    }

    return true;
}


?>