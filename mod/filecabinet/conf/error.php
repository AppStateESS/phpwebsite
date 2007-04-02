<?php
  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

translate('filecabinet');
$errors[FC_FILENAME_NOT_SET]       = _('Filename not set.');
$errors[FC_DIRECTORY_NOT_SET]      = _('Directory not set.');
$errors[FC_BOUND_FAILED]           = _('There was a problem loading the image file.');
$errors[FC_IMG_SIZE]               = _('Image was %sK making it larger than %sK size limit.');
$errors[FC_IMG_WIDTH]              = _('Image width was %spx, making it larger than %d pixel limit.');
$errors[FC_IMG_HEIGHT]             = _('Image height was %spx, making it larger than %d pixel limit.');
$errors[FC_IMG_WRONG_TYPE]         = _('Unacceptable image type.');
$errors[FC_IMG_NOT_FOUND]          = _('Image not found');
$errors[FC_DOCUMENT_SIZE]          = _('Document was %sK making it larger than %sK size limit.');
$errors[FC_DOCUMENT_WRONG_TYPE]    = _('Unacceptable document type.');
$errors[FC_DOCUMENT_NOT_FOUND]     = _('Document not found.');
$errors[FC_NO_UPLOAD]              = _('File not uploaded.');
$errors[FC_COULD_NOT_DELETE]       = _('Could not delete file.');
$errors[FC_IMAGE_DIMENSION]        = _('Image is %spx by %spx, the maximum dimensions are %spx by %spx.');
$errors[FC_FILE_MOVE]              = _('Unable to move the file to its new directory.');
$errors[FC_BAD_DIRECTORY]          = _('Could not write to directory.');
$errors[FC_MISSING_FOLDER]         = _('Missing folder information.');
$errors[FC_MAX_FORM_UPLOAD]        = _('File uploaded exceeded %sK size limit.');
$errors[FC_MISSING_TMP]            = _('Missing temporary upload directory.');
translate();
?>