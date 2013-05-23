<?php
/**
 * @version $Id$
 * @author Adam Morton
 * @author Steven Levin
 */

function export($formId = NULL) {
    if(!isset($formId)) {
        $message = dgettext('phatform', 'No form ID was passed');
        return new PHPWS_Error('phatform', 'export()', $message, 'continue', PHAT_DEBUG_MODE);
    }

    $exportDir = PHPWS_HOME_DIR . 'files/phatform/export/';
    $path = $exportDir;

    clearstatcache();
    if(!is_dir($path)) {
        if(is_writeable($exportDir)) {
            PHPWS_File::makeDir($path);
        } else {
            return PHPWS_Error::get(PHATFORM_EXPORT_PATH, 'phatform', 'Export.php::export()');
        }
    } elseif(!is_writeable($path)) {
        return PHPWS_Error::get(PHATFORM_EXPORT_PATH, 'phatform', 'Export.php::export()');
    }

    $sql = 'SELECT * FROM mod_phatform_form_' . $formId;
    $result = PHPWS_DB::getAll($sql);

    if(sizeof($result) > 0) {
        $data = '';
        foreach($result[0] as $key=>$value) {
            if($key != 'position')
            $data .= $key . "\t";
        }

        foreach($result as $entry) {
            $data .= "\n";
            foreach($entry as $key=>$value) {
                if($key != 'position') {
                    if($key == 'updated') {
                        $value = date(PHPWS_DATE_FORMAT . ' ' . PHPWS_TIME_FORMAT, $value);
                    } else {
                        $value = str_replace("\t", " ", $value);
                        $value = str_replace("\r\n", '', $value);
                        $value = str_replace("\n", '', $value);

                        $temp = $value;
                        if(is_array($temp)) {
                            $value = implode(',', $temp);
                        } else if (preg_match('/^[ao]:\d+:/', $temp)) {
                            // unserialize data
                            $unsTemp = unserialize($temp);
                            if(is_array($unsTemp)) {
                                $value = implode(',', $unsTemp);
                            } else {
                                $value = $unsTemp;
                            }
                        }
                    }

                    $data .= "$value\t";
                }
            }
        }
    }

    $filename = 'form_' . $formId . '_export.' . time() . '.csv';
    $file = fopen($path . $filename, 'w');
    fwrite($file, $data);
    fclose($file);

    $goCode = 'zip -qmj ' . $path . $filename . '.zip ' . $path . $filename;
    system($goCode);

    $filename = $filename . '.zip';
    $filepath = 'files/phatform/export/' . $filename;

    header('Pragma: public');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Last-Modified: '.gmdate ('D, d M Y H:i:s', filemtime ($filepath)).' GMT');
	header('Cache-Control: private',false);
	header('Content-Type: application/zip');
	header('Content-Disposition: attachment; filename="' . $filename . '"');
	header('Content-Transfer-Encoding: binary');
	header('Content-Length: '.filesize($filepath));
	header('Connection: close');
	readfile($filepath);
	exit();
}

?>