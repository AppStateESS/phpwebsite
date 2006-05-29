<?php

/**
 * Class assists with managing items needing approval
 *
 * @author Matt McNaney <matt at tux dot appstate dot edu>
 * @version $Id$
 */

class Version_Approval {
    var $module         = NULL;
    var $source_table   = NULL;
    var $version_table  = NULL;
    var $view_url       = NULL;
    var $edit_url       = NULL;
    var $approve_url    = NULL;
    var $disapprove_url = NULL;
    var $class_name     = NULL;
    var $view_method    = NULL;
    var $where          = NULL;
    var $columns        = array();
    var $standard       = array('id', 'source_id', 'vr_creator', 'vr_editor',
                                'vr_create_date', 'vr_edit_date', 'vr_number',
                                'vr_current', 'vr_approved', 'vr_locked');
    var $_db            = NULL;
  

    function Version_Approval($module, $table, $class_name=NULL, $view_method=NULL)
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
        $this->_db = & new PHPWS_DB($this->version_table);
    }


    function setModule($module)
    {
        $this->module = $module;
    }

    function setSourceTable($table)
    {
        $this->source_table = $table;
        $this->version_table = $this->source_table . VERSION_TABLE_SUFFIX;
    }

    function addWhere($column, $value=NULL, $operator=NULL, $conj=NULL, $group=NULL, $join=FALSE)
    {
        return $this->_db->addWhere($column, $value, $operator, $conj, $group, $join);
    }


    function setApproveUrl($approve_url)
    {
        $this->approve_url = $approve_url;
    }

    function setDisapproveUrl($disapprove_url)
    {
        $this->disapprove_url = $disapprove_url;
    }

    function setViewUrl($view_url)
    {
        $this->view_url = $view_url;
    }

    function setEditUrl($edit_url)
    {
        $this->edit_url = $edit_url;
    }

    function setColumns()
    {
        $this->columns = func_get_args();
    }

    function setClass($class_name)
    {
        $this->class_name = $class_name;
    }

    function setViewMethod($view_method)
    {
        $this->view_method = $view_method;
    }


    /**
     * Returns an array of unapproved versions. Mainly used privately
     * within class but may be called publically.
     */
    function get($obj_mode=TRUE)
    {
        $this->_db->addColumn('*');
        $this->_db->addColumn('users.username');
        $this->_db->addWhere('vr_approved', 0);
        $this->_db->addWhere('vr_creator', 'users.id');

        $result = $this->_db->select();

        if ($obj_mode) {
            if (PEAR::isError($result) || empty($result)) {
                return $result;
            }

            foreach ($result as $ver_arr) {
                $version = & new Version($this->source_table);
                $version->_plugInVersion($ver_arr);
                $listing[$version->id] = $version;
            }
            return $listing;
        } else {
            return $result;
        }
    }

    function getList($restrict_approval=TRUE)
    {
        if (!PHPWS_DB::isTable($this->version_table)) {
            return _('No items for approval.');
        }

        if (empty($this->approve_url) || empty($this->disapprove_url)) {
            return FALSE;
        }
    
        $result = $this->get(FALSE);


        if (PEAR::isError($result)) {
            return $result;
        }

        if (empty($result)) {
            return _('No items for approval.');
        }
        $temp_count = 0;
        foreach ($result as $app_item) {
            $links = array();

            $row_tpl['CREATE_DATE_LABEL'] = _('Created');
            $row_tpl['CREATE_DATE']       = strftime('%c', $app_item['vr_create_date']);

            $row_tpl['AUTHOR_LABEL']      = _('Author');
            $row_tpl['AUTHOR']            = $app_item['username'];
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
                $temp_obj = & new $this->class_name;
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
                                   _('Approve'));
        
                $links[] = sprintf('<a href="%s">%s</a>',
                                   $this->disapprove_url . '&amp;version_id=' . $app_item['id'],
                                   _('Disapprove'));
            }


            if (isset($this->view_url)) {
                $links[] = sprintf('<a href="%s">%s</a>',
                                   $this->view_url . '&amp;version_id=' . $app_item['id'], _('View'));
            }

            if (isset($this->edit_url)) {
                $links[] = sprintf('<a href="%s">%s</a>',
                                   $this->edit_url . '&amp;version_id=' . $app_item['id'], _('Edit'));
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
