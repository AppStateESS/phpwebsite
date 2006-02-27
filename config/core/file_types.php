<?php
/**
 * allowed image and document types
 *
 * make sure to BACKUP this file before making changes
 * $author Matt McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

$allowedImageTypes = array('image/jpeg',
			   'image/jpg',
			   'image/pjpeg',
			   'image/png',
			   'image/x-png',
			   'image/gif',
			   'image/wbmp');

$allowedDocumentTypes = array('doc'   => 'application/msword',
                              'pdf'   => 'application/pdf',
                              'xls'   => 'application/vnd.ms-excel',
                              'ppt'   => 'application/vnd.ms-powerpoint',
                              'gtar'  => 'application/x-gtar',
                              'tgz'   => 'application/x-gzip',
                              'js'    => 'application/x-javascript',
                              'swf'   => 'application/x-shockwave-flash',
                              'tar'   => 'application/x-tar',
                              'ustar' => 'application/x-ustar',
                              'xhtml' => 'application/xhtml+xml',
                              'xht'   => 'application/xhtml+xml',
                              'zip'   => 'application/zip',
                              'au'    => 'audio/basic',
                              'snd'   => 'audio/basic',
                              'mid'   => 'audio/midi',
                              'midi'  => 'audio/midi',
                              'mpga'  => 'audio/mpeg',
                              'mp2'   => 'audio/mpeg',
                              'mp3'   => 'audio/mpeg',
                              'm3u'   => 'audio/x-mpegurl',
                              'ram'   => 'audio/x-pn-realaudio',
                              'rm'    => 'audio/x-pn-realaudio',
                              'rpm'   => 'audio/x-pn-realaudio-plugin',
                              'ra'    => 'audio/x-realaudio',
                              'wav'   => 'audio/x-wav',
                              'gif'   => 'image/gif',
                              'jpeg'  => 'image/jpeg',
                              'jpg'   => 'image/jpeg',
                              'jpe'   => 'image/jpeg',
                              'png'   => 'image/png',
                              'tiff'  => 'image/tiff',
                              'tif'   => 'image/tiff',
                              'wbmp'  => 'image/vnd.wap.wbmp',
                              'css'   => 'text/css',
                              'html'  => 'text/html',
                              'htm'   => 'text/html',
                              'txt'   => 'text/plain',
                              'rtx'   => 'text/richtext',
                              'rtf'   => 'text/rtf',
                              'sgml'  => 'text/sgml',
                              'sgm'   => 'text/sgml',
                              'xsl'   => 'text/xml',
                              'xml'   => 'text/xml',
                              'mpeg'  => 'video/mpeg',
                              'mpg'   => 'video/mpeg',
                              'mpe'   => 'video/mpeg',
                              'qt'    => 'video/quicktime',
                              'mov'   => 'video/quicktime',
                              'mxu'   => 'video/vnd.mpegurl',
                              'avi'   => 'video/x-msvideo',
                              );

define('ALLOWED_IMAGE_TYPES', serialize($allowedImageTypes));
define('ALLOWED_DOCUMENT_TYPES', serialize($allowedDocumentTypes));

?>