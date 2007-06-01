<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

define('MAX_TN_IMAGE_WIDTH', 200);
define('MAX_TN_IMAGE_HEIGHT', 200);

define('FC_VIEW_MARGIN_WIDTH', 20);
define('FC_VIEW_MARGIN_HEIGHT', 100);

define('FC_UPLOAD_WIDTH', 450);
define('FC_UPLOAD_HEIGHT', 450);

define('FC_NONE_IMAGE_SRC', 'images/mod/filecabinet/none.png');

define('FC_MAX_IMAGE_POPUP_WIDTH', 1024);
define('FC_MAX_IMAGE_POPUP_HEIGHT', 768);

define('FC_THUMBNAIL_WIDTH', 100);
define('FC_THUMBNAIL_HEIGHT', 100);

define('FC_MAX_MULTIMEDIA_POPUP_WIDTH', 1024);
define('FC_MAX_MULTIMEDIA_POPUP_HEIGHT', 768);


/**
 * if true (the default) and a file already has a resized version,
 * the image manager will pick it automatically.
 * If false, then the image manager will create a new resized version
 */
define('RESIZE_IMAGE_USE_DUPLICATE', true);


define('FC_INCOMING_DIRECTORY', 'files/filecabinet/incoming/');

?>