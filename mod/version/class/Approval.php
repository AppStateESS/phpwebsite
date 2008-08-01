<?php

/**
 * Class assists with managing items needing approval
 *
 * @author Matt McNaney <matt at tux dot appstate dot edu>
 * @version $Id$
 */

class Version_Approval {
    public $module         = NULL;
    public $source_table   = NULL;
    public $version_table  = NULL;
    public $view_url       = NULL;
    public $edit_url       = NULL;
    public $approve_url    = NULL;
    public $disapprove_url = NULL;
    public $class_name     = NULL;
    public $view_method    = NULL;
    public $where          = NULL;
    public $columns        = array();
    public $standard       = array('id', 'source_id', 'vr_creator', 'vr_editor',
                                'vr_create_date', 'vr_edit_date', 'vr_number',
                                'vr_current', 'vr_approved', 'vr_locked');
    private $_db           = NULL;


    public function __construct($module, $table, $class_name=NULL, $view_method=NULL)
    {
        $this->setModule($module);
        $this->setSourceTable($table);
        if (class_exists($class_name)) {
            $methods = get_class_methods($class_name);
            $this->setClass($class_name);
            if (in_array(strtolower($view_method), $methods)) {
                $this->setViewMethod($view_method);
            }
        }
        $this->_db = new PHPWS_DB($this->version_table);
    }


    public function setModule($module)
    {
        $this->module = $module;
    }

    public function setSourceTable($table)
    {
        $this->source_table = $table;
        $this->version_table = $this->source_table . VERSION_TABLE_SUFFIX;
    }

    public function addWhere($column, $value=NULL, $operator=NULL, $conj=NULL, $group=NULL, $join=FALSE)
    {
        return $this->_db->addWhere($column, $value, $operator, $conj, $group, $join);
    }


    public function setApproveUrl($approve_url)
    {
        $this->approve_url = $approve_url;
    }

    public function setDisapproveUrl($disapprove_url)
    {
        $this->disapprove_url = $disapprove_url;
    }

    public function setViewUrl($view_url)
    {
        $this->view_url = $view_url;
    }

    public function setEditUrl($edit_url)
    {
        $this->edit_url = $edit_url;
    }

    public function setColumns()
    {
        $this->columns = func_get_args();
    }

    public function setClass($class_name)
    {
        $this->class_name = $class_name;
    }

    public function setViewMethod($view_method)
    {
        $this->view_method = $view_method;
    }


    /**
     * Returns an array of unapproved versions. Mainly used privately
     * within class but may be called publically.
     */
    public function get($obj_mode=TRUE)
    {
        if (!empty($this->columns)) {
            foreach ($this->columns as $value) {
                $this->_db->addColumn($value);
            }
        } else {
            $this->_db->addColumn('*');
        }

        $this->_db->addColumn('users.username');
        $this->_db->addWhere('vr_approved', 0);
        $this->_db->addJoin('left', $this->_db->tables[0], 'users', 'vr_creator', 'id');

        $result = $this->_db->select();

        if ($obj_mode) {
            if (PEAR::isError($result) || empty($result)) {
                return $result;
            }

            foreach ($result as $ver_arr) {
                $version = new Version($this->source_table);
                $version->_plugInVersion($ver_arr);
                $listing[$version->id] = $version;
            }
            return $listing;
        } else {
            return $result;
        }
    }

    public function getList($restrict_approval=TRUE)
    {

        if (!PHPWS_DB::isTable($this->version_table)) {
            $msg = dgettext('version', 'No items for approval.');

            return $msg;
        }

        if (empty($this->approve_url) || empty($this->disapprove_url)) {
            return FALSE;
        }

        $result = $this->get(FALSE);


        if (PEAR::isError($result)) {
            return $result;
        }

        if (empty($result)) {
            $msg =  dgettext('version', 'No items for approval.');

            return $msg;
        }
        $temp_count = 0;
        foreach ($result as $app_item) {
            $links = array();

            $row_tpl['CREATE_DATE_LABEL'] = dgettext('version', 'Created');
            $row_tpl['CREATE_DATE']       = strftime('%c', $app_item['vr_create_date']);

            $row_tpl['AUTHOR_LABEL']      = dgettext('version', 'Author');
            if (!empty($app_item['username'])) {
                $row_tpl['AUTHOR']        = $app_item['username'];
            } else {
                $row_tpl['AUTHOR']        = dgettext('version', 'Anonymous');
            }
            // prevent the repeat
            unset($app_item['username']);

            $keys = array_keys($app_item);
            if (!empty($this->columns)) {
                $show_cols = array_intersect($this->columns, $keys);
            } else {
                $show_cols = array_diff($keys, $this->standard);
            }

            $count = 0;

            if (!empty($this->class_name) && !empty($this->view_method)) {
                $temp_obj = new $this->class_name;
                PHPWS_Core::plugObject($temp_obj, $app_item);

                $result = $temp_obj->{$this->view_method}();

                if (PEAR::isError($result)) {
                    return $result;
                } elseif (empty($result)) {
                    continue;
                }

                $template_file = 'alternate_view.tpl';
                $row_tpl['ALT_VIEW'] = $result;
            } else {
                $template_file = 'approval_list.tpl';
                foreach ($show_cols as $show_tag) {
                    $count++;

                    $row_tpl['COLUMN_LABEL_' . $count] = $show_tag;
                    $row_tpl['COLUMN_' . $count] = $app_item[$show_tag];
                }
            }

            if (!$restrict_approval || !Current_User::isRestricted($this->module)) {
                $links[] = sprintf('<a href="%s">%s</a>',
                                   $this->approve_url . '&amp;version_id=' . $app_item['id'],
                                   dgettext('version', 'Approve'));

                $links[] = sprintf('<a href="%s">%s</a>',
                                   $this->disapprove_url . '&amp;version_id=' . $app_item['id'],
                                   dgettext('version', 'Disapprove'));
            }


            if (isset($this->view_url)) {
                $links[] = sprintf('<a href="%s">%s</a>',
                                   $this->view_url . '&amp;version_id=' . $app_item['id'], dgettext('version', 'View'));
            }

            if (isset($this->edit_url)) {
                $links[] = sprintf('<a href="%s">%s</a>',
                                   $this->edit_url . '&amp;version_id=' . $app_item['id'], dgettext('version', 'Edit'));
            }

            if (!empty($links)) {
                $row_tpl['LINKS'] = implode(' | ', $links);
            }

            $template['approval-rows'][$temp_count] = $row_tpl;
            $temp_count++;
        }

        return PHPWS_Template::process($template, 'version', $template_file);
    }

}

?>
