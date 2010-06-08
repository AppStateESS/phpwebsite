<?php

/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

class Profiler_Division {
    public $id    = 0;
    public $title = NULL;
    public $show_homepage = 1;
    public $error = NULL;

    public function Profiler_Division($id=0) {
        if (empty($id)) {
            return;
        }

        $this->id = (int)$id;
        $result =  $this->init();
        if (Core\Error::isError($result)) {
            $this->error = $result;
        } elseif (!$result) {
            $this->error = Core\Error::get(Core\DB_EMPTY_SELECT, 'core', 'Profiler_Division::constructor', 'ID: ' . $id);
        }
    }

    public function init()
    {
        $db = new Core\DB('profiler_division');
        $db->addWhere('id', $this->id);
        return $db->loadObject($this);
    }

    public function getTags()
    {
        $js_vars['height']  = '200';
        $js_vars['address'] = sprintf('index.php?module=profiler&amp;command=edit_division&division_id=%s&authkey=%s',
        $this->id, Current_User::getAuthKey());
        $js_vars['label']   = dgettext('profiler', 'Edit');
        $links[] = javascript('open_window', $js_vars);

        if (Current_User::allow('profiler', 'delete_divisions')) {
            $js_vars = array();
            $js_vars['address']  = sprintf('index.php?module=profiler&amp;command=delete_division&division_id=%s&authkey=%s',
            $this->id, Current_User::getAuthKey());
            $js_vars['link']     = dgettext('profiler', 'Delete');
            $js_vars['question'] = dgettext('profiler', 'Deleting this division will remove all the profiles under it.\nAre you sure you want to do this?');
            $links[] = javascript('confirm', $js_vars);
        }

        $tpl['ACTION'] = implode(' | ', $links);
        return $tpl;
    }

    public function post()
    {
        if (UTF8_MODE) {
            $this->title = preg_replace('/[^\w\pL\s]/u', '', $_POST['title']);
        } else {
            $this->title = preg_replace('/[^\w\s]/u', '', $_POST['title']);
        }

        $db = new Core\DB('profiler_division');
        $db->addWhere('title', $this->title);
        $db->addWhere('id', $this->id, '!=');
        if ($db->select('one')) {
            return FALSE;
        }

        if (empty($this->title)) {
            return FALSE;
        }
        return TRUE;
    }

    public function save()
    {
        $db = new Core\DB('profiler_division');
        return $db->saveObject($this);
    }

    public function viewLink()
    {
        $vars['user_cmd'] = 'view_div';
        $vars['div_id'] = $this->id;
        return Core\Text::moduleLink($this->title, 'profiler', $vars);
    }

    public function delete()
    {
        $db = new Core\DB('profiler_division');
        $db->addWhere('id', $this->id);
        if (!Core\Error::logIfError($db->delete())) {
            $db = new Core\DB('profiles');
            $db->addWhere('profile_type', $this->id);
            return !Core\Error::logIfError($db->delete());
        }
    }

}

?>