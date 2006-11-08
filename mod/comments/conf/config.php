<?php
  /**
   * Comment's define config
   *
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

define('DEFAULT_ANONYMOUS_TITLE', _('Anonymous')); // What an anonymous poster is called
define('COMMENT_DATE_FORMAT', '%c');

// Maximum dimensions of an user's avatar graphic
define('COMMENT_MAX_AVATAR_WIDTH', 150);
define('COMMENT_MAX_AVATAR_HEIGHT', 150);


/**
 * There is alternate set of templates for comments
 * The alternate set uses avatar images.
 */
define('COMMENT_VIEW_TEMPLATE', 'alt_view.tpl');
define('COMMENT_VIEW_ONE_TPL', 'alt_view_one.tpl');

//define('COMMENT_VIEW_TEMPLATE', 'view.tpl');
//define('COMMENT_VIEW_ONE_TPL', 'view_one.tpl');


// This phrase will appear if a person doesn't enter a subject line
// for their comment.
define('COMMENT_NO_SUBJECT', _('No subject'));

// 10, 20, or 50 comments per page.
define('COMMENT_DEFAULT_LIMIT', 20);

?>