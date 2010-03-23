<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

define('LAYOUT_THEME_EXEC', true);

define('DEFAULT_BOX_VAR', 'DEFAULT');
define('DEFAULT_THEME_VAR', 'BODY');
define('DEFAULT_CONTENT_VAR', '_MAIN');
define('MAX_ORDER_VALUE', 99999);
define('PAGE_TITLE_DIVIDER', ' - ');

// If FALSE, module style sheets will not be loaded
define('LAYOUT_ALLOW_STYLE_LINKS', TRUE);


/**
 * If set to true, the theme's content type will be application/xhtml_xml
 * instead of text/html. This is a good method of testing compatibility.
 */
define('XML_MODE', false);


/**
 * If true, Layout will prevent users from using the site until
 * they enable cookies in their browser. May cause problems
 * with crawlers.
 */
define('LAYOUT_CHECK_COOKIE', false);


/**
 * Changing this to true will cause javascript to parse module javascript
 * and use the copy from the module directory. This will NOT WORK branch sites.
 */
define('LAYOUT_FORCE_MOD_JS', false);

/**
 * If true, layout doesn't bother with a javascript check, it just
 * assumes the user has it activated.
 */
define('LAYOUT_IGNORE_JS_CHECK', false);

?>