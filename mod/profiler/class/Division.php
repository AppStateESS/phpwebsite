<?php

  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

class Profiler_Division {
    var $id    = 0;
    var $title = NULL;
    var $show_homepage = 1;
    var $error = NULL;

    function Profiler_Division($id=0) {
        if (empty($id)) {
            return;
        }

        $this->id = (int)$id;
        $result =  $this->init();
        if (PEAR::isError($result)) {
            $this->error = $result;
        } elseif (!$result) {
            $this->error = PHPWS_Error::get(PHPWS_DB_EMPTY_SELECT, 'core', 'Profiler_Division::constructor', 'ID: ' . $id);
        }
    }

    function init()
    {
        $db = new PHPWS_DB('profiler_division');
        $db->addWhere('id', $this->id);
        return $db->loadObject($this);
    }

    function getTags()
    {
        $js_vars['height']  = '200';
        $js_vars['address'] = sprintf('index.php?module=profiler&amp;command=edit_division&division_id=%s&authkey=%s', 
                                      $this->id, Current_User::getAuthKey());
        $js_vars['label']   = dgettext('profiler', 'Edit');
        $links[] = javascript('open_window', $js_vars);

        $tpl['ACTION'] = implode(' | ', $links);
        return $tpl;
    }

    function post()
    {
        $this->title = @preg_replace('/[^\w\s]/u', '', $_POST['title']);

        $db = new PHPWS_DB('profiler_division');
        $db->addWhere('title', $this->title);
        if ($db->select('one')) {
            return FALSE;
        }
        
        if (empty($this->title)) {
            return FALSE;
        }
        return TRUE;
    }

    function save()
    {
        $db = new PHPWS_DB('profiler_division');
        return $db->saveObject($this);
    }

    function viewLink()
    {
        $vars['user_cmd'] = 'view_div';
        $vars['div_id'] = $this->id;
        return PHPWS_Text::moduleLink($this->title, 'profiler', $vars);
    }

}

?>