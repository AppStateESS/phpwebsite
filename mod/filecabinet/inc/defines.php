<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

/**
 * Folder defines must match file associations for 
 * basic types.
 */
define('IMAGE_FOLDER',      1);
define('DOCUMENT_FOLDER',   2);
define('MULTIMEDIA_FOLDER', 3);

/**
 * File association types
 */
define('FC_IMAGE',           1);
define('FC_DOCUMENT',        2);
define('FC_MEDIA',           3);
define('FC_IMAGE_FOLDER',    4);
define('FC_DOCUMENT_FOLDER', 5);
define('FC_IMAGE_RANDOM',    6);
define('FC_IMAGE_RESIZE',    7);
define('FC_MEDIA_RESIZE',    8);
define('FC_IMAGE_CROP',      9);

/**
 * Error defines
 */
define('FC_FILENAME_NOT_SET',       -100);
define('FC_DIRECTORY_NOT_SET',      -101);
define('FC_BOUND_FAILED',           -102);
define('FC_IMG_SIZE',               -103);
define('FC_IMG_HEIGHT',             -104);
define('FC_IMG_WIDTH',              -105);
define('FC_IMG_WRONG_TYPE',         -106);
define('FC_IMG_NOT_FOUND',          -107);
define('FC_DOCUMENT_SIZE',          -108);
define('FC_DOCUMENT_WRONG_TYPE',    -109);
define('FC_DOCUMENT_NOT_FOUND',     -110);
define('FC_NO_UPLOAD',              -111);
define('FC_COULD_NOT_DELETE',       -112);
define('FC_IMAGE_DIMENSION',        -113);
define('FC_FILE_MOVE',              -114);
define('FC_BAD_DIRECTORY',          -115);
define('FC_MISSING_FOLDER',         -116);
define('FC_MAX_FORM_UPLOAD',        -117);
define('FC_MISSING_TMP',            -118);
define('FC_MULTIMEDIA_SIZE',        -119);
define('FC_MULTIMEDIA_WRONG_TYPE',  -120);
define('FC_MULTIMEDIA_NOT_FOUND',   -121);
define('FC_THUMBNAIL_NOT_WRITABLE', -122);
define('FC_FFMPEG_NOT_FOUND',       -123);
define('FC_DUPLICATE_FILE',         -124);
define('FC_FILE_TYPE_MISMATCH',     -125);


?>