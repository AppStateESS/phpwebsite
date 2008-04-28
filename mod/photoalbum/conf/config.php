<?php

/**
 * @version $Id$
 * @author  Steven Levin <steven at NOSPAM tux[dot]appstate[dot]edu>
 */

define('PHOTOALBUM_DIR', 'images/photoalbum/');

define('PHOTOALBUM_TN_WIDTH', 150);
define('PHOTOALBUM_TN_HEIGHT', 150);

define('PHOTOALBUM_DEBUG_MODE', 0);

define('PHOTOALBUM_MAX_UPLOADS', 10);

/* 0 for newest first, 1 for oldest */
define('PHOTOALBUM_DEFAULT_SORT', 1);

define('PHOTOALBUM_MAX_WIDTH', 640);
define('PHOTOALBUM_MAX_HEIGHT', 800);

/* auto-resize originals, true or false */
define("PHOTOALBUM_RS", TRUE);
/* max width/height of resized originals */
define("PHOTOALBUM_RS_WIDTH", 1280);
define("PHOTOALBUM_RS_HEIGHT", 1280);

define('ALLOWED_IMAGE_TYPES', serialize(array('image/jpeg', 'image/gif', 'image/bmp', 'image/png',
                                              'image/pjpeg', 'image/x-png')));
?>