<?php
  /**
   * Phatform conversion file
   *
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function convert()
{
    $mod_list = PHPWS_Core::installModList();

    if (!in_array('phatform', $mod_list)) {
        return _('Form Generator is not installed locally.');
    }
   
    if (!Convert::isConverted('phatform_elements')) {
        return convertElements();
    } elseif (!Convert::isConverted('phatform')) {
        return convertPhatforms();
    } else {
        return _('Form Generator has already been converted.');
    }
}

function convertElements()
{
    $error = false;

    $db = Convert::getSourceDB('mod_phatform_checkbox');
    if (empty($db)) {
        return _('Form Generator does not appear to be installed in the source database.');
    }

    $checkbox = $db->export(false);
    $tbl_prefix = Convert::getTblPrefix();

    if (!empty($checkbox)) {
        if (PEAR::isError($checkbox)) {
            PHPWS_Error::log($checkbox);
            return _('An error occurred when trying to copy your mod_phatform_checkbox table.');
        }

        if (!empty($tbl_prefix)) {
            $checkbox = str_replace($tbl_prefix . 'mod_phatform', 'mod_phatform', $checkbox);
        }
        $db->disconnect();
        Convert::siteDB();

        if (PHPWS_DB::import($checkbox, false)) {
            createSeqTable('mod_phatform_checkbox');
            $content[] = _('Check box table imported successfully.');
        } else {
            $error = true;
            $content[] = _('Check box table failed to copy successfully.');
            return implode('<br />', $content);
        }
    }

    $db = Convert::getSourceDB('mod_phatform_dropbox');
    $dropbox = $db->export(false);
    if (!empty($dropbox)) {
        if (PEAR::isError($dropbox)) {
            PHPWS_Error::log($dropbox);
            return _('An error occurred when trying to copy your mod_phatform_dropbox table.');
        }

        if (!empty($tbl_prefix)) {
            $dropbox = str_replace($tbl_prefix . 'mod_phatform', 'mod_phatform', $dropbox);
        }
        $db->disconnect();
        Convert::siteDB();

        if (PHPWS_DB::import($dropbox, false)) {
            createSeqTable('mod_phatform_dropbox');
            $content[] = _('Drop box table imported successfully.');
        } else {
            $error = true;
            $content[] = _('Drop box table failed to copy successfully.');
            return implode('<br />', $content);
        }
    }

    $db = Convert::getSourceDB('mod_phatform_multiselect');
    $multiselect = $db->export(false);

    if (!empty($multiselect)) {
        if (PEAR::isError($multiselect)) {
            PHPWS_Error::log($multiselect);
            return _('An error occurred when trying to copy your mod_phatform_multiselect table.');
        }


        if (!empty($tbl_prefix)) {
            $multiselect = str_replace($tbl_prefix . 'mod_phatform', 'mod_phatform', $multiselect);
        }
        $db->disconnect();
        Convert::siteDB();

        if (PHPWS_DB::import($multiselect, false)) {
            createSeqTable('mod_phatform_multiselect');
            $content[] = _('Multi-select table imported successfully.');
        } else {
            $error = true;
            $content[] = _('Multi-select table failed to copy successfully.');
            return implode('<br />', $content);
        }
    }

    $db = Convert::getSourceDB('mod_phatform_options');
    $options = $db->export(false);

    if (!empty($options)) {
        if (PEAR::isError($options)) {
            PHPWS_Error::log($options);
            return _('An error occurred when trying to copy your mod_phatform_options table.');
        }

        if (!empty($tbl_prefix)) {
            $options = str_replace($tbl_prefix . 'mod_phatform', 'mod_phatform', $options);
        }
        $db->disconnect();
        Convert::siteDB();

        if (PHPWS_DB::import($options, false)) {
            createSeqTable('mod_phatform_options');
            $content[] = _('Options table imported successfully.');
        } else {
            $error = true;
            $content[] = _('Options table failed to copy successfully.');
            return implode('<br />', $content);
        }
    }


    $db = Convert::getSourceDB('mod_phatform_radiobutton');
    $radiobutton = $db->export(false);

    if (!empty($radiobutton)) {
        if (PEAR::isError($radiobutton)) {
            PHPWS_Error::log($radiobutton);
            return _('An error occurred when trying to copy your mod_phatform_radiobutton table.');
        }

        if (!empty($tbl_prefix)) {
            $radiobutton = str_replace($tbl_prefix . 'mod_phatform', 'mod_phatform', $radiobutton);
        }
        $db->disconnect();
        Convert::siteDB();

        if (PHPWS_DB::import($radiobutton, false)) {
            createSeqTable('mod_phatform_radiobutton');
            $content[] = _('Radio button table imported successfully.');
        } else {
            $error = true;
            $content[] = _('Radio button table failed to copy successfully.');
            return implode('<br />', $content);
        }
    }


    $db = Convert::getSourceDB('mod_phatform_textarea');
    $textarea = $db->export(false);
    if (!empty($textarea)) {
        if (PEAR::isError($textarea)) {
            PHPWS_Error::log($textarea);
            return _('An error occurred when trying to copy your mod_phatform_textarea table.');
        }

        if (!empty($tbl_prefix)) {
            $textarea = str_replace($tbl_prefix . 'mod_phatform', 'mod_phatform', $textarea);
        }
        $db->disconnect();
        Convert::siteDB();

        if (PHPWS_DB::import($textarea, false)) {
            createSeqTable('mod_phatform_textarea');
            $content[] = _('Text area table imported successfully.');
        } else {
            $error = true;
            $content[] = _('Text area table failed to copy successfully.');
            return implode('<br />', $content);
        }
    }

    $db = Convert::getSourceDB('mod_phatform_textfield');
    $textfield = $db->export(false);
    if (!empty($textfield)) {
        if (PEAR::isError($textfield)) {
            PHPWS_Error::log($textfield);
            return _('An error occurred when trying to copy your mod_phatform_textfield table.');
        }

        if (!empty($tbl_prefix)) {
            $textfield = str_replace($tbl_prefix . 'mod_phatform', 'mod_phatform', $textfield);
        }
        $db->disconnect();
        Convert::siteDB();

        if (PHPWS_DB::import($textfield, false)) {
            createSeqTable('mod_phatform_textfield');
            $content[] = _('Text field table imported successfully.');
        } else {
            $error = true;
            $content[] = _('Text field table failed to copy successfully.');
            return implode('<br />', $content);
        }
    }

    if ($error) {
        $content[] = _('One or more errors occurred while copying over Form Generator tables. Please check your logs, clear your database tables, and try again.');
    } else {
        Convert::addConvert('phatform_elements');
        $content[] = sprintf('<a href="index.php?command=convert&package=phatform">%s</a>',
                             _('Continue conversion. . .'));
    }

    return implode('<br />', $content);
}


function convertPhatforms()
{
    $db = Convert::getSourceDB('mod_phatform_forms');
    if (!$db) {
        return _('Form Generator is not installed in the supplied database.');
    }

    $result = $db->select();
    $db->disconnect();
    Convert::siteDB();

    if (empty($result)) {
        return _('No forms to convert.');
    } else {
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            return _('An unrecoverable error occurred. Check your logs.');
        } 
        $tbl_prefix = Convert::getTblPrefix();
        foreach ($result as $row) {
            $form_table_name = 'mod_phatform_form_' . $row['id'];
            $db = Convert::getSourceDB($form_table_name);
            if (!$db) {
                continue;
            } elseif (PEAR::isError($db)) {
                PHPWS_Error::log($db);
                continue;
            } else {
                $form_data = $db->export();
                if (!empty($tbl_prefix)) {
                    $form_data = str_replace($tbl_prefix . 'mod_phatform', 'mod_phatform', $form_data);
                }

                $db->disconnect();
                Convert::siteDB();
                $result = PHPWS_DB::import($form_data);
                if (PEAR::isError($result)) {
                    PHPWS_Error::log($result);
                    continue;
                }
            }

            $savedb = new PHPWS_DB('mod_phatform_forms');
            $key = new Key;

            $key->setModule('phatform');
            $key->setItemName('form');
            $key->setItemId($row['id']);
            $key->setEditPermission('edit_forms');
            $key->setUrl('index.php?module=phatform&PHAT_MAN_OP=view&PHPWS_MAN_ITEMS[]=' . $row['id']);

            if ($row['anonymous']) {
                $key->restricted = 0;
            } else {
                $key->restricted = 1;
            }
        
            $key->setTitle(utf8_encode($row['label']));
            $key->setSummary($row['blurb0']);
            $key->save();
            $row['key_id'] = $key->id;

            $savedb->addValue($row);
            $result = $savedb->insert(false);

            $savedb->reset();
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
            }
            createSeqTable($form_table_name);
        }
    }

    createSeqTable('mod_phatform_forms');
    Convert::addConvert('phatform');
    $content[] = _('Form generator forms converted and keyed!');
    $content[] = _('All done!');
    $content[] = sprintf('<a href="index.php">%s</a>',
                         _('Return to main page.'));
    return implode('<br />', $content);
}


function createSeqTable($table)
{
    $db = new PHPWS_DB($table);
    return $db->updateSequenceTable();
}

?>