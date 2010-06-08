<?php

/**
 * Individual group objects
 *
 * @author Matt McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

class PHPWS_Group {
    public $id           = NULL;
    public $name         = NULL;
    public $user_id      = 0;
    public $active       = FALSE;
    public $_members     = NULL;
    public $_permissions = NULL;
    public $_groups      = NULL;
    public $_error       = NULL;

    public function __construct($id=NULL, $loadGroups=TRUE)
    {
        if (isset($id)){
            $this->setId($id);
            $result = $this->init();
            if (core\Error::isError($result)){
                $this->_error = $result;
                return;
            }
            $this->loadMembers();
            if ($loadGroups == TRUE)
            $this->loadGroups();
        }
    }

    public function init()
    {
        $db = new \core\DB('users_groups');
        return $db->loadObject($this);
    }

    public function setId($id)
    {
        $this->id = (int)$id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setActive($active)
    {
        $this->active = (bool)$active;
    }

    public function isActive()
    {
        return (bool)$this->active;
    }

    public function loadGroups()
    {
        $DB = new \core\DB('users_members');
        $DB->addWhere('member_id', $this->getId());
        $DB->addColumn('group_id');
        $result = $DB->select('col');

        if (core\Error::isError($result)){
            \core\Error::log($result);
            return;
        }

        $this->setGroups($result);
    }

    public function setGroups($groups)
    {
        $this->_groups = $groups;
    }

    public function getGroups()
    {
        return $this->_groups;
    }

    public function loadMembers()
    {
        $db = new \core\DB('users_members');
        $db->addWhere('group_id', $this->getId());
        $db->addColumn('member_id');
        $result = $db->select('col');
        $this->setMembers($result);
    }

    public function setName($name, $test=FALSE)
    {
        if ($test == TRUE){
            if (empty($name) || preg_match('/[^\w\s]+/', $name))
            return \core\Error::get(USER_ERR_BAD_GROUP_NAME, 'users', 'setName');

            if (strlen($name) < GROUPNAME_LENGTH)
            return \core\Error::get(USER_ERR_BAD_GROUP_NAME, 'users', 'setName');

            $db = new \core\DB('users_groups');
            $db->addWhere('name', $name);
            $db->addWhere('id', $this->id, '!=');
            $result = $db->select('one');
            if (isset($result)){
                if(core\Error::isError($result))
                return $result;
                else
                return \core\Error::get(USER_ERR_DUP_GROUPNAME, 'users', 'setName');
            } else {
                $this->name = $name;
                return TRUE;
            }
        } else {
            $this->name = $name;
            return TRUE;
        }
    }

    public function getName()
    {
        return $this->name;
    }

    public function setUserId($id)
    {
        $this->user_id = $id;
    }

    public function getUserId()
    {
        return $this->user_id;
    }

    public function setMembers($members)
    {
        $this->_members = $members;
    }

    public function dropMember($member)
    {
        if (!is_array($this->_members))
        return;

        $key = array_search($member, $this->_members);
        unset($this->_members[$key]);
    }

    public function dropAllMembers()
    {
        $db = new \core\DB('users_members');
        $db->addWhere('group_id', $this->getId());
        return $db->delete();
    }

    public function clearMembership()
    {
        $db = new \core\DB('users_members');
        $db->addWhere('member_id', $this->getId());
        return $db->delete();
    }

    public function addMember($member, $test=FALSE)
    {
        if ($test == TRUE) {
            $db = new \core\DB('users_groups');
            $db->addWhere('id', $member);
            $result = $db->select('one');
            if (core\Error::logIfError($result)) {
                return false;
            } elseif (!$result) {
                \core\Error::log(USER_ERR_GROUP_DNE, 'users', 'addMember');
                return false;
            } else {
                $this->_members[] = $member;
                return true;
            }
        } else {
            $this->_members[] = $member;
        }
    }

    public function getMembers()
    {
        return $this->_members;
    }

    public function save()
    {
        $db = new \core\DB('users_groups');

        $result = $db->saveObject($this);
        $members = $this->getMembers();

        if (isset($members)){
            $this->dropAllMembers();
            $db = new \core\DB('users_members');
            foreach($members as $member){
                $db->addValue('group_id', $this->getId());
                $db->addValue('member_id', $member);
                \core\Error::logIfError($db->insert());
                $db->resetValues();
            }
        }
    }

    public function kill()
    {
        $db = new \core\DB('users_groups');
        $db->addWhere('id', $this->id);
        $db->delete();
        $this->dropAllMembers();
        $this->clearMembership();
        $this->dropPermissions();
    }


    public function dropPermissions()
    {
        $modules = \core\Core::getModules(true, true);
        if (empty($modules)) {
            return false;
        }

        foreach ($modules as $mod) {
            $permTable = Users_Permission::getPermissionTableName($mod);

            $db = new \core\DB($permTable);
            if (!$db->isTable($permTable)) {
                continue;
            }

            $db->addWhere('group_id', $this->id);
            \core\Error::logIfError($db->delete());

            $db = new \core\DB('phpws_key_edit');
            $db->addWhere('group_id', $this->id);
            \core\Error::logIfError($db->delete());

            $db = new \core\DB('phpws_key_view');
            $db->addWhere('group_id', $this->id);
            \core\Error::logIfError($db->delete());
        }
        return true;
    }

    public function allow($module, $permission=NULL, $item_id=NULL, $itemname=NULL)
    {
        \core\Core::initModClass('users', 'Permission.php');

        if (!isset($this->_permissions)) {
            $this->loadPermissions();
        }

        return $this->_permission->allow($module, $permission, $item_id, $itemname);
    }

    public function getPermissionLevel($module)
    {
        \core\Core::initModClass('users', 'Permission.php');

        if (!isset($this->_permission)) {
            $this->loadPermissions();
        }

        return $this->_permission->getPermissionLevel($module);
    }

    public function loadPermissions($loadAll=TRUE)
    {
        if ($loadAll && isset($this->_groups)) {
            $groups = $this->_groups;
        }

        $groups[] = $this->getId();
        $this->_permission = new Users_Permission($groups);
    }

    public function getTplTags()
    {
        $this->loadMembers();
        $id = $this->id;

        $linkVar['action'] = 'admin';
        $linkVar['group_id'] = $id;

        $linkVar['command'] = 'edit_group';
        $links[] = \core\Text::secureLink(core\Icon::show('edit'), 'users', $linkVar, NULL, dgettext('users', 'Edit Group'));

        $linkVar['command'] = 'setGroupPermissions';
        $links[] = \core\Text::secureLink(core\Icon::show('permission'), 'users', $linkVar);
        $linkVar['command'] = 'manageMembers';
        $links[] = \core\Text::secureLink(core\Icon::show('users', dgettext('users', 'Members')), 'users', $linkVar);

        $linkVar['command'] = 'remove_group';
        $removelink['ADDRESS'] = \core\Text::linkAddress('users', $linkVar, TRUE);
        $removelink['QUESTION'] = dgettext('users', 'Are you SURE you want to remove this group?');
        $removelink['LINK'] = \core\Icon::show('delete', dgettext('users', 'Remove'));
        $links[] = Layout::getJavascript('confirm', $removelink);

        $template['ACTIONS'] = implode('', $links);

        $members = $this->getMembers();

        if (isset($members)) {
            $template['MEMBERS'] = count($members);
        }
        else {
            $template['MEMBERS'] = 0;
        }

        return $template;
    }

}

?>