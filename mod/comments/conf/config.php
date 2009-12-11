<?php
  /**
   * Comment's define config
   *
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

define('DEFAULT_ANONYMOUS_TITLE', dgettext('comments', 'Anonymous')); // What an anonymous poster is called

/**
 * If you allow anonymous users to post and enter their name, you may
 * want to mark their name somehow. The COMMENT_ANONYMOUS_TAG _only_ appears
 * if the user was anonymous. You can make it a word, image, or just an activation
 * space.
 */
define('COMMENT_ANONYMOUS_TAG', dgettext('comments', '(Anon)'));


define('COMMENT_DATE_FORMAT', '%c');

// Maximum dimensions of an user's avatar graphic
define('COMMENT_MAX_AVATAR_WIDTH', 80);
define('COMMENT_MAX_AVATAR_HEIGHT', 80);


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
define('COMMENT_NO_SUBJECT', dgettext('comments', 'No subject'));

// 25, 50, or 100 comments per page.
define('COMMENT_DEFAULT_LIMIT', 25);

define('CM_LOCK_IMAGE', '<img src="' . PHPWS_SOURCE_HTTP . 'mod/comments/img/lock.png" width="50" height="50" />');

?>
