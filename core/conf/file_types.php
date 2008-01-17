<?php
/**
 * allowed image and document types
 *
 * Some extensions can have different mime types depending
 * on platform. To retain index integrity, the extensions may not 
 * actually exist.
 *
 * make sure to BACKUP this file before making changes
 * $author Matt McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */
$allowedImageTypes = array('jpeg' => 'image/jpeg',
			   'jpe'  => 'image/jpeg',
			   'jpg'  => 'image/jpg',
			   'pjpeg'=> 'image/pjpeg',
			   'png'  => 'image/png',
			   'xpng' => 'image/x-png',
			   'gif'  => 'image/gif',
			   'bmp'  => 'image/wbmp'
                           );

$allowedMultimediaTypes = array('wav'  => 'audio/x-wav',
                                'mp3'  => 'audio/mpeg',
                                'wma'  => 'audio/x-ms-wma',
                                'wax'  => 'audio/x-ms-wax',
                                'flv'  => 'video/x-flv',
                                'flv1' => 'application/x-extension-flv',
                                'flv2' => 'application/x-flash-video',
                                'avi'  => 'video/x-msvideo',
                                'mov'  => 'video/quicktime',
                                'mpeg' => 'video/mpeg',
                                'mpg'  => 'video/mpeg',
                                'mpe'  => 'video/mpeg',
                                'asf'  => 'video/x-ms-asf',
                                'asx'  => 'video/x-ms-asf',
                                'wvx'  => 'video/x-ms-wvx',
                                'wm'   => 'video/x-ms-wm',
                                'wmx'  => 'video/x-ms-wmx',
                                'wmv'  => 'video/x-ms-wmv',
                                'wmz'  => 'application/x-ms-wmz',
                                'wmd'  => 'application/x-ms-wmd'
                                );


/**
 * Alternative types
 * 'xlsx'  => 'application/vnd.openxmlformats',
 * 'docx'  => 'application/vnd.openxmlformats',
 * 'pptx'  => 'application/vnd.openxmlformats',
 */
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
                              'wmz'   => 'application/x-ms-wmz',
                              'wmd'   => 'application/x-ms-wmd',
                              'xlsx'  => 'application/x-zip',
                              'docx'  => 'application/x-zip',
                              'pptx'  => 'application/x-zip',
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
                              'wma'   => 'audio/x-ms-wma',
                              'wax'   => 'audio/x-ms-wax',
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
                              'asf'   => 'video/x-ms-asf',
                              'asx'   => 'video/x-ms-asf',
                              'wvx'   => 'video/x-ms-wvx',
                              'wm'    => 'video/x-ms-wm',
                              'wmx'   => 'video/x-ms-wmx',
                              'mpeg'  => 'video/mpeg',
                              'mpg'   => 'video/mpeg',
                              'mpe'   => 'video/mpeg',
                              'qt'    => 'video/quicktime',
                              'mov'   => 'video/quicktime',
                              'mxu'   => 'video/vnd.mpegurl',
                              'avi'   => 'video/x-msvideo',
                              'wmv'   => 'video/x-ms-wmv'
                              );

define('ALLOWED_IMAGE_TYPES', serialize($allowedImageTypes));
define('ALLOWED_DOCUMENT_TYPES', serialize($allowedDocumentTypes));
define('ALLOWED_MULTIMEDIA_TYPES', serialize($allowedMultimediaTypes));

?>