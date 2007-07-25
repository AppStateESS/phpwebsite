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
    // Versions previous to 1.5.0 removed 25Jul2007.
    switch (1) {
    case version_compare($version, '1.5.0', '<'):
        $content[] = '<pre>Core version prior to version 1.5.0 are not supported. Please download version 1.5.2 or earlier.</pre>';
        break;

    case version_compare($version, '1.5.1', '<'):
        $content[] = '<pre>1.5.1 changes
--------------
+ Added error check to mysql.php file to prevent over-valued CHARs
+ Fixed bug #1708507. Extra string character.
+ Database - bug #1713219 - Added auto detect parameter to insert
             function in saveObject
</pre>';

    case version_compare($version, '1.5.2', '<'):
        $content[] = '<pre>1.5.2 changes
--------------
+ Added setAnchor function to DBPager.
</pre>';

    case version_compare($version, '1.6.0', '<'):
        $files = array('conf/formConfig.php', 'conf/version.php',
                       'conf/file_types.php');
        $content[] = '<pre>';

        if (PHPWS_Boost::updateFiles($files, 'core')) {
            $content[] = "--- Files copied successfully:";

        } else {
            $content[] = "--- Failed to copy files:";
        }

        $content[] = "    " . implode("\n    ", $files);
        $content[] = '';
        $content[] = '
1.6.0 changes
--------------
Class files
---------------------------------
Core.php
+ Log error check failed if the file didn\'t exist. Fixed it.
+ Changed some error messages for log file writing


Database.php
+ Small change on increment to prevent "column_name + -1". I don\'t think
  it matters but I prefer a cleaner approach with "column_name -1"
+ If the increment amount is 0, function returns true.
+ Fixed some error messages. wrong function named
+ Added setLock, lockTables, and unlockTables functions. Factory
  files updated as well though postgresql is untested.
+ Added fourth parameter to addColumn. Lets you perform single
  column counts
+ The reset function now cleans out the tables array leaving only
  the initial table
+ Added missing NOT IN and NOT BETWEEN where conditions.


DBPager.php
+ changed \'page\' variable to \'pg\' to try and prevent
  problems with mod_rewrite
+ Fixed bug #1747940 - changed dbpager direction arrows.


Debug.php
+ Boolean true was showing up as "1" with test.


Editor.php
+ Added ability to set editor width and height


File.php
+ Added getFileExtension function. Returns the last characters after
  the period on a filename. 


Form.php
+ Default rows and columns for text areas now comes for the form
  config file.
+ Changed Editor and Form to default to normal text areas if the
  editor is missing.
+ Changed to reflect width and height changes in Editor
+ Added setOptgroup function for select and multiple form elements


Key.php
+ Added a little code to strip a url from an link if sent by mistake


Mail.php
+ Added single and double quotes to the allowed characters in the address


XMLParser.php
+ Moved out of the RSS module into the core.
+ Added a "format" function to better synthesis XML information 


Javascript
----------------------------------------
+ Tinymce editor - changed method for determining height and width
+ Ajax - Hopefully fixed some goofiness with the loadRequester
+ Changed javascript calendar\'s default form to phpws_form
+ FCKeditor and Tinymce now use width and height settings from Editor
+ Open Window lets you pick a button or link interface
+ Added some extra instruction to javascript\'s select_confirm
  documentation.


Conversion
-----------------------------------------
+ Patched category image conversion with Eloi\'s fix (#1742987)


Documents
-----------------------------------------
+ Updated CREDITS
+ Fixed bad directions in Language.txt
+ Forms.txt - added directions for use of setOptgroup in Form class

Themes
-----------------------------------------
+ Fixed float-left and float-right margins


Other
-----------------------------------------
+ Previous phpwebsite versions had a config/core directory. It was a
  complete copy of the core/conf directory. This is no longer the
  case. Now Setup copies that directory on install.
+ Added define to setup config.tpl turn off table lock ability for
  those that don\'t have those permissions.
+ Added yet another mime type for FLV files to file_types.php
+ Removed the compatibility mode from config.tpl in Setup
+ Added getid3 library for use in File Cabinet
+ Added addslashes to the source directory setting for those wacky
  windows folk.
+ Added Danish translation files for core supplied by Claus Iversen.
+ Related is no longer a core module.
</pre>';
    }
    return true;
}


?>