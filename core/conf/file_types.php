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
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

$all_file_types['asf']['mct'][] = 'text/plain';
$all_file_types['asf']['mct'][] = 'video/x-ms-asf';
$all_file_types['asf']['fi'][]  = 'microsoft asf';
$all_file_types['asf']['vb']    = 'Microsoft ASF Video';

$all_file_types['avi']['mct'][] = 'video/x-msvideo';
$all_file_types['avi']['fi'][]  = 'data, avi';
$all_file_types['avi']['vb']    = 'AVI Video';

$all_file_types['css']['mct'][] = 'text/plain';
$all_file_types['css']['mct'][] = 'text/css';
$all_file_types['css']['fi'][]  = 'text/x-c';
$all_file_types['css']['vb']    = 'Cascading Style Sheet';

$all_file_types['csv']['mct'][] = 'text/plain';
$all_file_types['csv']['fi'][]  = 'text/plain';
$all_file_types['csv']['vb']    = 'Comma Separated Value Text';

$all_file_types['doc']['mct'][] = 'application/msword';
$all_file_types['doc']['fi'][]  = 'microsoft installer';
$all_file_types['doc']['vb']    = 'Microsoft Word Document';

$all_file_types['docx']['mct'][] = 'application/x-zip';
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

$all_file_types['jpg']['mct'][] = 'image/jpg';
$all_file_types['jpg']['fi'][]  = 'jpg image data';
$all_file_types['jpg']['vb']    = 'JPG Image';
$all_file_types['jpg']['base']  = 'jpg';

$all_file_types['jpeg'] = & $all_file_types['jpg'];
$all_file_types['jpe']  = & $all_file_types['jpg'];

$all_file_types['js']['mct'][] = 'text/plain';
$all_file_types['js']['mct'][] = 'application/x-javascript';
$all_file_types['js']['fi'][]  = 'text/x-c';
$all_file_types['js']['vb']    = 'Javascript';

$all_file_types['mid']['mct'][] = 'audio/unknown';
$all_file_types['mid']['mct'][] = 'audio/midi';
$all_file_types['mid']['fi'][]  = 'standard midi';
$all_file_types['mid']['vb']    = 'MIDI';

$all_file_types['midi'] = & $all_file_types['mid'];

$all_file_types['mov']['mct'][] = 'video/quicktime';
$all_file_types['mov']['fi'][]  = 'apple quicktime';
$all_file_types['mov']['vb']    = 'Apple Quicktime Video';
$all_file_types['mov']['base']  = 'mov';

$all_file_types['qt'] = & $all_file_types['mov'];

$all_file_types['mp3']['mct'][] = 'text/plain';
$all_file_types['mp3']['mct'][] = 'audio/mpeg';
$all_file_types['mp3']['fi'][]  = 'mpeg adts, layer iii';
$all_file_types['mp3']['fi'][]  = 'mp3 encoding';
$all_file_types['mp3']['vb']    = 'MPEG-3 Audio';

$all_file_types['mp4']['mct'][] = 'video/mp4';
$all_file_types['mp4']['mct'][] = 'video/mpeg';
$all_file_types['mp4']['fi'][]  = 'iso media, mpeg v4';
$all_file_types['mp4']['vb']    = 'MPEG-4 Video';

$all_file_types['mpg']['mct'][] = 'video/mpv';
$all_file_types['mpg']['mct'][] = 'video/mpeg';
$all_file_types['mpg']['fi'][]  = 'mpeg sequence,';
$all_file_types['mpg']['vb']    = 'MPEG Video';

$all_file_types['mpeg'] = & $all_file_types['mpg'];
$all_file_types['mpe'] = & $all_file_types['mpg'];

$all_file_types['odb']['mct'][] = 'application/x-zip';
$all_file_types['odb']['mct'][] = 'application/vnd.oasis.opendocument.database';
$all_file_types['odb']['fi'][]  = 'application/octet-stream';
$all_file_types['odb']['vb']    = 'OpenOffice.Org Database';

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
$all_file_types['png']['fi'][]  = 'png image data';
$all_file_types['png']['vb']    = 'PNG Image';

$all_file_types['ppt']['mct'][] = 'application/vnd.ms-powerpoint';
$all_file_types['ppt']['bl']    = true;
$all_file_types['ppt']['fi'][]  = 'microsoft installer';
$all_file_types['ppt']['vb']    = 'Microsoft Powerpoint Presentation';

$all_file_types['pptx']['mct'][] = 'application/x-zip';
$all_file_types['pptx']['mct'][] = 'application/vnd.openxmlformats';
$all_file_types['pptx']['fi'][]  = 'zip archive data';
$all_file_types['pptx']['vb']    = 'Microsoft Powerpoint 2007 Presentation';

$all_file_types['rm']['mct'][] = 'application/vnd.rn-realmedia';
$all_file_types['rm']['fi'][]  = 'realmedia';
$all_file_types['rm']['vb']    = 'RealMedia Multimedia';

$all_file_types['rpm']['mct'][] = 'application/x-rpm';
$all_file_types['rpm']['fi'][]  = '^rpm';
$all_file_types['rpm']['vb']    = 'RPM Package';

$all_file_types['rtf']['mct'][] = 'text/rtf';
$all_file_types['rtf']['fi'][]  = 'rich text';
$all_file_types['rtf']['vb']    = 'Rich Text Format';

$all_file_types['swf']['mct'][] = 'application/x-shockwave-flash';
$all_file_types['swf']['fi'][]  = 'macromedia flash data';
$all_file_types['swf']['vb']    = 'Macromedia ShockWave Flash';

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

$all_file_types['wav']['mct'][] = 'audio/x-wav';
$all_file_types['wav']['fi'][]  = 'wave audio';
$all_file_types['wav']['vb']    = 'WAV Audio';

$all_file_types['wmv']['mct'][] = 'video/x-ms-wmv';
$all_file_types['wmv']['mct'][] = 'text/plain';
$all_file_types['wmv']['fi'][]  = 'Microsoft ASF';
$all_file_types['wmv']['vb']    = 'WMV Video';

$all_file_types['xls']['mct'][] = 'application/vnd.ms-excel';
$all_file_types['xls']['bl']    = true;
$all_file_types['xls']['fi'][]  = 'microsoft installer';
$all_file_types['xls']['vb']    = 'Microsoft Excel Spreadsheet';

$all_file_types['xlsx']['mct'][] = 'application/x-zip';
$all_file_types['xlsx']['mct'][] = 'application/vnd.openxmlformats';
$all_file_types['xlsx']['fi'][]  = 'zip archive data';
$all_file_types['xlsx']['vb']    = 'Microsoft Excel 2007 Spreadsheet';

$all_file_types['xml']['mct'][] = 'text/xml';
$all_file_types['xml']['fi'][]  = 'xml ';
$all_file_types['xml']['vb']    = 'XML Document';


$all_file_types['zip']['mct'][] = 'application/x-zip';
$all_file_types['zip']['fi'][]  = 'zip archive';
$all_file_types['zip']['vb']    = 'Compressed Zip Archive';

?>