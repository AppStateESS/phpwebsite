<?php

/**
 * This is the listing of file types known by phpwebsite. It is
 * not complete but sufficient for basic use.
 * File Cabinet uses this file to determine a file's mime type. Without
 * it, File Cabinet will refuse a file's inclusion to the system.
 *
 * If you want to add a file you need to know how fileinfo and
 * mime_content_type identify it.
 *
 * The key for each info array is a file's extension. Some extensions
 * are repeated and pointed to (e.g. qt is a copy of mov).
 *
 * mct : array of types that mime_content_type may return on a file
 * fi  : an sample of the text fileinfo returns on a file. These
 *       text snippets are run through a preg_match to compare.
 * vb  : Verbose description of the file.
 * bl  : Sometimes mime_content_type will return null on a file.
 *       (Microsoft files in unix for example). If bl is set, the
 *       file can get by.
 * base: If a file can be uploaded with an alternate extension, the base
 *       indicates the parent extension.
 *
 * Some servers may have problems identifying your file type. If so, you
 * try adding the examples below to your list. Be that unwanted file types
 * could get through. Know your users. Substitute your file extension
 * for the 'ext' in the array.
 *
 * // a very common type
 * $all_file_types['ext']['mct'] = 'text/plain';
 *
 * // If mime_content_type is not working at all and file -iL
 * // can't identify your filetype, this is what you get.
 * $all_file_types['ext']['mct'] = 'application/octet-stream';
 *
 *
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

$all_file_types['csv']['mct'][] = 'text/plain';
$all_file_types['csv']['fi'][]  = 'text/plain';
$all_file_types['csv']['vb']    = 'Comma Separated Value Text';

$all_file_types['doc']['mct'][] = 'application/msword';
$all_file_types['doc']['mct'][] = 'application/vnd.ms-office';
$all_file_types['doc']['fi'][]  = 'microsoft installer';
$all_file_types['doc']['fi'][]  = 'microsoft office document';
$all_file_types['doc']['vb']    = 'Microsoft Word Document';

$all_file_types['docx']['mct'][] = 'application/x-zip';
$all_file_types['docx']['mct'][] = 'application/zip';
$all_file_types['docx']['mct'][] = 'application/vnd.openxmlformats';
$all_file_types['docx']['fi'][]  = 'zip archive data';
$all_file_types['docx']['vb']    = 'Microsoft Word 2007 Document';

$all_file_types['flv']['mct'][] = 'text/plain';
$all_file_types['flv']['mct'][] = 'application/x-flash-video';
$all_file_types['flv']['mct'][] = 'application/x-extension-flv';
$all_file_types['flv']['mct'][] = 'video/x-flv';
$all_file_types['flv']['fi'][]  = 'macromedia flash video';
$all_file_types['flv']['vb']    = 'Macromedia Flash Video';

$all_file_types['gif']['mct'][] = 'image/gif';
$all_file_types['gif']['fi'][]  = 'gif image data';
$all_file_types['gif']['vb']    = 'GIF Image';

$all_file_types['gz']['mct'][] = 'text/plain';
$all_file_types['gz']['mct'][] = 'application/x-gzip';
$all_file_types['gz']['fi'][]  = 'gzip compressed';
$all_file_types['gz']['vb']    = 'Gzip Compressed Archive ';

$all_file_types['jpg']['mct'][] = 'image/jpg';
$all_file_types['jpg']['mct'][] = 'image/jpeg';
$all_file_types['jpg']['mct'][] = 'image/pjpeg';
$all_file_types['jpg']['fi'][]  = 'jpeg image data';
$all_file_types['jpg']['vb']    = 'JPG Image';
$all_file_types['jpg']['base']  = 'jpg';

$all_file_types['jpeg'] = & $all_file_types['jpg'];

$all_file_types['mp3']['mct'][] = 'text/plain';
$all_file_types['mp3']['mct'][] = 'audio/mpeg';
$all_file_types['mp3']['fi'][]  = 'mpeg adts, layer iii';
$all_file_types['mp3']['fi'][]  = 'mp3 encoding';
$all_file_types['mp3']['vb']    = 'MPEG-3 Audio';

$all_file_types['mp4']['mct'][] = 'video/mp4';
$all_file_types['mp4']['mct'][] = 'video/mpeg';
$all_file_types['mp4']['fi'][]  = 'iso media, mpeg v4';
$all_file_types['mp4']['vb']    = 'MPEG-4 Video';

$all_file_types['odp']['mct'][] = 'application/x-zip';
$all_file_types['odp']['mct'][] = 'application/vnd.oasis.opendocument.presentation';
$all_file_types['odp']['fi'][]  = 'application/octet-stream';
$all_file_types['odp']['vb']    = 'OpenOffice.Org Presentation';

$all_file_types['ods']['mct'][] = 'application/x-zip';
$all_file_types['ods']['mct'][] = 'application/vnd.oasis.opendocument.spreadsheet';
$all_file_types['ods']['fi'][]  = 'application/octet-stream';
$all_file_types['ods']['vb']    = 'OpenOffice.Org Spreadsheet';

$all_file_types['odt']['mct'][] = 'application/x-zip';
$all_file_types['odt']['mct'][] = 'application/vnd.oasis.opendocument.text';
$all_file_types['odt']['fi'][]  = 'application/octet-stream';
$all_file_types['odt']['vb']    = 'OpenOffice.Org Text';

$all_file_types['ogg']['mct'][] = 'application/ogg';
$all_file_types['ogg']['fi'][]  = 'ogg data';
$all_file_types['ogg']['vb']    = 'OGG Audio';

$all_file_types['pdf']['mct'][] = 'application/pdf';
$all_file_types['pdf']['fi'][]  = 'pdf document';
$all_file_types['pdf']['vb']    = 'Acrobat PDF Document';

$all_file_types['png']['mct'][] = 'image/png';
$all_file_types['png']['mct'][] = 'text/plain';
$all_file_types['png']['fi'][]  = 'png image data';
$all_file_types['png']['vb']    = 'PNG Image';

$all_file_types['ppt']['mct'][] = 'application/vnd.ms-powerpoint';
$all_file_types['ppt']['mct'][] = 'application/vnd.ms-office';
$all_file_types['ppt']['mct'][] = 'application/msword';
$all_file_types['ppt']['bl']    = true;
$all_file_types['ppt']['fi'][]  = 'microsoft installer';
$all_file_types['ppt']['vb']    = 'Microsoft Powerpoint Presentation (ppt)';

$all_file_types['pptx']['mct'][] = 'application/x-zip';
$all_file_types['pptx']['mct'][] = 'application/zip';
$all_file_types['pptx']['mct'][] = 'application/vnd.openxmlformats';
$all_file_types['pptx']['fi'][]  = 'zip archive data';
$all_file_types['pptx']['vb']    = 'Microsoft Powerpoint Presentation (pptx)';

$all_file_types['rtf']['mct'][] = 'text/rtf';
$all_file_types['rtf']['fi'][]  = 'rich text';
$all_file_types['rtf']['vb']    = 'Rich Text Format';

$all_file_types['tar']['mct'][] = 'application/x-tar';
$all_file_types['tar']['fi'][]  = 'application/x-tar';
$all_file_types['tar']['vb']    = 'Tarball Archive';

$all_file_types['tgz']['mct'][] = 'text/plain';
$all_file_types['tgz']['mct'][] = 'application/x-gzip';
$all_file_types['tgz']['fi'][]  = 'gzip compressed';
$all_file_types['tgz']['vb']    = 'Compressed Tarball Archive';

$all_file_types['txt']['mct'][] = 'text/plain';
$all_file_types['txt']['fi'][]  = 'text/plain';
$all_file_types['txt']['vb']    = 'Text';

$all_file_types['webm']['mct'][] = 'video/webm';
$all_file_types['webm']['mct'][] = 'audio/webm';
$all_file_types['webm']['fi'][]  = 'video/webm';
$all_file_types['webm']['vb']    = 'WebM Video';

$all_file_types['xls']['mct'][] = 'application/vnd.ms-excel';
$all_file_types['xls']['mct'][] = 'application/msword';
$all_file_types['xls']['bl']    = true;
$all_file_types['xls']['fi'][]  = 'microsoft installer';
$all_file_types['xls']['fi'][]  = 'microsoft office document';
$all_file_types['xls']['vb']    = 'Microsoft Excel Spreadsheet';

$all_file_types['xlsx']['mct'][] = 'application/x-zip';
$all_file_types['xlsx']['mct'][] = 'application/vnd.openxmlformats';
$all_file_types['xlsx']['fi'][]  = 'zip archive data';
$all_file_types['xlsx']['vb']    = 'Microsoft Excel 2007 Spreadsheet';

$all_file_types['zip']['mct'][] = 'application/x-zip';
$all_file_types['zip']['fi'][]  = 'zip archive';
$all_file_types['zip']['vb']    = 'Compressed Zip Archive';

?>