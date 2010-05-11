<?php
/**
 * Config file for template class
 *
 * @author Matt McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */


/*********************** TEMPLATES *****************************/
/**
 * Setting FORCE_THEME_TEMPLATES to TRUE forces the template class
 * to ONLY look for template files in your current theme. When FALSE
 * the template class will first look in your theme then in the
 * templates/ directory. When FALSE, the template class has to make
 * sure the file is in the theme. If you know for sure, it is then
 * setting this to TRUE will save a file check.
 */

define('FORCE_THEME_TEMPLATES', false);

/**
 * Normally, if the the Pear template class can't fill in at least one
 * tag in a template, it will return NULL. Setting the below to TRUE,
 * causes the phpWebSite to still return template WITHOUT the tag
 * substitutions. This should normally be set to FALSE unless you are
 * testing code.
 */

define('RETURN_BLANK_TEMPLATES', true);

/**
 * If you want template to prefix the templates it is using with an
 * information tag, set the below to TRUE.
 * DO NOT leave this set to TRUE or use this on a live server
 * as it reveals your installation path.
 */

define('LABEL_TEMPLATES', false);
?>