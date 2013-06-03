<?php

/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
function core_update(&$content, $version)
{
    $content[] = '';
    // Versions previous to 1.9.8 removed 2 May, 2013.
    switch (1) {
        case version_compare($version, '1.9.8', '<'):
            $content[] = '<h2>Sorry</h2>
<p>Your version of phpWebSite is too old to update using 1.8.0. Please update to
1.7.3 and return.</p>';

        case version_compare($version, '2.0.0', '<'):
            if (PHPWS_Core::isBranch()) {
                $content[] = 'This update can only be performed on the hub.';
                return false;
            }
            if (!PHPWS_Boost::inBranch()) {
                $config_dir = PHPWS_SOURCE_DIR . 'config/core/';


                if (!is_writable($config_dir)) {
                    $content[] = '<p>Core update can not continue until your hub installation\'s <strong>config/core/</strong> directory is writable.</p>';
                    return false;
                }

                $source_http = sprintf("<?php\ndefine('PHPWS_SOURCE_HTTP', '%s');\n?>",
                        PHPWS_CORE::getHomeHttp());
                if (!file_put_contents($config_dir . 'source.php', $source_http)) {
                    $content[] = '<p>Could not create config/core/source.php file.</p>';
                    return false;
                }

                $content[] = <<<EOT
                <pre>2.0.0 changes
-----------------
+ Hub/Branch overhaul. Branches pull config, templates, javascript,
  and theme files from hub instead of locally.
+ Added Icon class. Standardizes icons and prevents overlap.
+ Added Tag class: extendable class used with Image and Form2.
+ Added tag_implode function.
+ Created Form2 class.
+ Added CKeditor.
+ Added Lightbox.
+ getConfigFile does not throw error now.
+ Dutch translation updated.
+ Added autoload function for core classes.
+ Source dir derived from file path and not simply "./"
+ Added Image class.
+ Critical functions changed to throw exceptions.
+ Setup steamlined.</pre>

<p><strong>Note:</strong> this update creates a backup of your config/core/config.php file named<br />
config-prior170.php.<br />
If your installation is working, this file may be safely deleted.</p>
<p>IMPORTANT! Many settings in the old config.php have been moved to core/conf/defines.php in the hub.
You can delete all settings <strong>except</strong> the following:</p>
<ul><li>PHPWS_SOURCE_DIR</li>
<li>PHPWS_HOME_DIR</li>
<li>PHPWS_SOURCE_HTTP</li>
<li>SITE_HASH</li>
<li>PHPWS_DSN</li>
<li>PHPWS_TABLE_PREFIX</li></ul>

EOT;
            }
            if ($branch = PHPWS_Boost::inBranch(true)) {
                if (!PHPWS_File::copy_directory(PHPWS_SOURCE_DIR . 'javascript/editors/fckeditor/',
                                $branch->directory . 'javascript/editors/fckeditor',
                                true)) {
                    mkdir($branch->directory . 'images/ckeditor/');
                    $this->content[] = dgettext('branch',
                            'Failed to copy FCKeditor to branch.');
                } else {
                    $content[] = 'FCKeditor not copied to branch. Check directory permissions.';
                }
            } else {
                mkdir(PHPWS_SOURCE_DIR . 'images/ckeditor/');
            }

        case version_compare($version, '2.0.1', '<'):
            $content[] = <<<UPDATES
            <pre>2.0.1 changes
----------------------
+ Fixed captcha trying to pull from branch directory.
+ Fixed templates ignoring use_hub_themes setting.
+ Image class will accept tilde in source directory.
+ Database fix for insert and update on multiple tables.
+ Powerpoint types added to file check.
+ Background mode added to index (allows for selective loading on Ajax calls)
+ Freecap ereg_replace updated to preg_replace
+ Fix for js_calendar
+ Fix for javascript alert script
+ Fixes for Fckeditor and Tinymce
+ Inclusion of ngBackup
</pre>
UPDATES;
        case version_compare($version, '2.0.2', '<'):
            $content[] = <<<UPDATES
<pre>2.0.2 changes
----------------------
+ Added To top icon
+ Fixed table bug where names used in foreign key constrains (in CREATE TABLE statements) were not prefixed correctly
+ Removed HTML from some translations.
+ Fixed bug causing a table name to be repeated in a JOIN statement
+ Fixed some PHP notice errors.
+ Fixed some hub icon directory problems (Thanks Eloi).
+ Image resizing reworked to correct problems with irregular images.
+ ngBackup updates.
+ Fixed some templating issues (Thanks Tommy, Eloi).
+ URL validity checking in Text was made more robust.
+ Fixed some label id issues in form.
+ Fixed a loadDimensions error in Image (Thanks Eloi)
+ Fixed DBPager duplicating table insertions (Thanks Eloi)
+ Fixed some PEAR PHP 5 warnings.
+ Changed URL forwarding - if first value after the module is numeric, it is cast as an id.
+ Removed deny-all .htaccess file from file directory.
</pre>
UPDATES;
        case version_compare($version, '2.1.0', '<'):
            $content[] = <<<UPDATES
<pre>2.1.0 changes
----------------------
 + Another Powerpoint file mimetype added to file_types.php
 + Turned off collapse_urls as a default condition.
 + Added cosign configuration example in defines.dist.php
 + More static warnings silenced.

Classes
----------------
 + Form.php
    - Fixed bug with addCheckAssoc
    - Added onbeforeclose protection to forms
    - Fixed array handling in PHPWS_Form::grab(). If the array of elements isn't
      indexed by integers, it will return the entire array instead of trying to
      return element 0.
 + Error.php
    - Pear method call was called all lowercase. Probably a hold over
      from when function name case was irrelevant.
 + Cookie.php
    - Cookie assumes a cookie is set before deletion. Changed function
      call to check prior to operation.
 + File.php
    - Fix to file extension checking.
 + Core.php
    - Moved some logic for finding the site Base URL out of Layout and
      into Core, as the getBaseURL() function
 + Text.php
    - Missing variable added to parameter list.
    - Faulty parse_url is being silenced on failure and getGetValues returns null.
    - Filtering out high ascii using parseOutput
 + Init.php
    - Fixed overwrite problem with defines.php on updating.
 + Database.php
    - Removed restrictive join check
    - Database substitutes table "as" if it exists on column call and/or set order
    - Allow parenthesis and commas in addOrder, so we can order by function,
      like "order by coalesce(...)"
    - Fixed splat usage with count in addColumn and getColumn
+ DBPager.php
    - Rewrote CSV parser to use fputcsv
    - Fixed bug with csv reporting

Javascript
------------------
+ captcha/recaptcha - added recycle instructions
+ required_input - file inputs can now be required fields
+ protect_form - new javascript to prevent user leaving fields blank
+ jquery - updated
+ flowplayer - updated version
+ editors
    + ckeditor - added File Cabinet functionality
    + cleditor - added Cleditor by Hilmar
</pre>
UPDATES;

        case version_compare($version, '2.1.1', '<'):
            $content[] = <<<UPDATES
<pre>
2.1.1 changes
-----------------

Core Classes
-----------------
+ Fixed bugs with Key and Database. Registered users (not deities) were having problems
  with editing and view restricted items. PHPWS_DB::groupIn rewritten.
+ Core now has better error messages for Branches problems.
+ Image - removed px from width and height for xhtml compatibility.
+ Static notice fixes
+ Removed clone function call in Icon.

Javascript
----------------
+ jquery_ui and jquery updated
</pre>
UPDATES;

        case version_compare($version, '2.2.0', '<'):
            $changes = file_get_contents(PHPWS_SOURCE_DIR . 'core/boost/changes/2_2_0.txt');
            $content[] = "<pre>$changes</pre>";

        case version_compare($version, '2.3.1', '<'):
            try {
                include PHPWS_SOURCE_DIR . 'core/boost/updates/2_3_0.php';
                update_core_2_3_0();
            } catch (\Exception $e) {
                echo $e->getCode();
                $content[] = 'Error: ' . $e->getMessage();
                return false;
            }
            $changes = file_get_contents(PHPWS_SOURCE_DIR . 'core/boost/changes/2_3_0.txt');
            $content[] = "<pre>$changes</pre>";
    }
    return true;
}

function coreUpdateFiles($files, &$content)
{
    if (PHPWS_Boost::updateFiles($files, 'core')) {
        $content[] = '--- Updated the following files:';
        $good = true;
    } else {
        $content[] = '--- Unable to update the following files:';
        $good = false;
    }
    $content[] = "     " . implode("\n     ", $files);
    $content[] = '';
    return $good;
}

?>