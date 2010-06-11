<?php

/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

class Version_Restore {
    public $source_table  = NULL;
    public $version_table = NULL;
    public $source_id     = 0;
    public $backup_list   = NULL;
    public $error         = NULL;
    public $class_name    = NULL;
    public $view_method   = NULL;
    public $remove_url    = NULL;
    public $restore_url   = NULL;
    public $columns       = array();
    public $standard       = array('id', 'source_id', 'vr_creator', 'vr_editor',
                                'vr_create_date', 'vr_edit_date', 'vr_number',
                                'vr_current', 'vr_approved', 'vr_locked');


    public function __construct($module, $source_table, $source_id, $class_name=NULL, $view_method=NULL) {
        $this->module = $module;
        $this->source_table = $source_table;
        $this->version_table = $this->source_table . VERSION_TABLE_SUFFIX;
        $this->source_id = (int)$source_id;
        if (!empty($class_name)) {
            $this->class_name = $class_name;
        }

        if (!empty($view_method)) {
            $this->view_method = $view_method;
        }

        $this->loadBackupList();
    }

    public function loadBackupList(){
        if (empty($this->source_id)) {
            return FALSE;
        }

        $db = new PHPWS_DB($this->version_table);
        $db->addWhere('source_id', $this->source_id);
        $db->addWhere('vr_approved', 1);
        $db->addWhere('vr_current', 0);
        $db->addColumn('*');
        $db->addColumn('users.username');
        $db->addWhere('vr_creator', 'users.id');
        $db->addOrder('vr_number desc');
        $result = $db->select();

        if (empty($result)) {
            return NULL;
        } elseif ( PHPWS_Error::isError($result) ) {
            $this->error = $result;
            return;
        }
        $this->backup_list = &$result;
    }

    public function setColumns()
    {
        $this->columns = func_get_args();
    }

    public function setRestoreUrl($url)
    {
        $this->restore_url = $url;
    }

    public function setRemoveUrl($url)
    {
        $this->remove_url = $url;
    }

    public function getList()
    {

        if ( !PHPWS_DB::isTable($this->version_table) || empty($this->backup_list) ) {
            $msg = dgettext('version', 'No backup versions available.');

            return $msg;
        }

        $temp_count = 0;
        foreach ($this->backup_list as $version) {
            $links = array();
            $row_tpl['CREATE_DATE_LABEL'] = dgettext('version', 'Created');
            $row_tpl['CREATE_DATE']       = strftime('%c', $version['vr_create_date']);

            $row_tpl['AUTHOR_LABEL']      = dgettext('version', 'Author');
            $row_tpl['AUTHOR']            = $version['username'];
            // prevent the repeat
            unset($version['username']);

            $keys = array_keys($version);
            if (!empty($this->columns)) {
                $show_cols = array_intersect($this->columns, $keys);
            } else {
                $show_cols = array_diff($keys, $this->standard);
            }

            $count = 0;

            if (!empty($this->class_name) && !empty($this->view_method)) {
                $temp_obj = new $this->class_name;
                PHPWS_Core::plugObject($temp_obj, $version);

                $result = $temp_obj->{$this->view_method}();

                if (PHPWS_Error::isError($result)) {
                    return $result;
                } elseif (empty($result)) {
                    continue;
                }

                $template_file = 'alt_restore_list.tpl';
                $row_tpl['ALT_VIEW'] = $result;
            } else {
                $template_file = 'restore_list.tpl';
                foreach ($show_cols as $show_tag) {
                    $count++;

                    $row_tpl['COLUMN_LABEL_' . $count] = $show_tag;
                    $row_tpl['COLUMN_' . $count] = $version[$show_tag];
                }
            }

            if (Current_User::isUnrestricted($this->module)) {
                $links[] = sprintf('<a href="%s">%s</a>',
                $this->restore_url . '&amp;version_id=' . $version['id'], dgettext('version', 'Restore'));

                $jsvars['QUESTION'] = dgettext('version', 'Are you sure you want to remove this version?');
                $jsvars['ADDRESS']  = $this->remove_url . '&amp;version_id=' . $version['id'];
                $jsvars['LINK']     = dgettext('version', 'Remove');
                $links[] = javascript('confirm', $jsvars);
            }

            $row_tpl['LINKS'] = implode(' | ', $links);
            $template['restore-rows'][$temp_count] = $row_tpl;
            $temp_count++;
        }

        if (empty($template)) {
            $msg = dgettext('version', 'A problem occurred when trying to process the restoration list.');

            return $msg;
        } else {

            return PHPWS_Template::process($template, 'version', $template_file);
        }
    }
}

?>