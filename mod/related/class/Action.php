<?php

/**
 * Administrative options within the related module
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

class Related_Action {

    public static function create(Related $related)
    {
        $template['TITLE_LBL'] = dgettext('related', 'Title');
        $template['MODULE_LBL'] = dgettext('related', 'Module');

        $instructions[] = dgettext('related', 'Currently, nothing is associated with this item.');
        $instructions[] = dgettext('related', 'If you want to add related information to this item, click the "Build Related" link.');

        $template['INSTRUCTIONS'] = implode('<br />', $instructions);

        $vars['action'] = 'start';
        $vars['key'] = $related->key_id;

        $template['LINK'] = PHPWS_Text::secureLink(dgettext('related', 'Build Related'), 'related', $vars);
        $template['TITLE'] = $related->getUrl(TRUE);

        $module = new PHPWS_Module($related->_key->module);
        $template['MODULE'] = $module->getProperName(TRUE);
        return PHPWS_Template::process($template, 'related', 'create.tpl');
    }

    public static function edit(Related $current)
    {
        PHPWS_Core::initCoreClass('Module.php');
        $related = Related_Action::getBank();
        $template['TITLE_LBL'] = dgettext('related', 'Title');
        $template['MODULE_LBL'] = dgettext('related', 'Module');
        $template['TITLE'] = $related->getUrl(TRUE);

        $id = $related->id;

        $js['question'] = dgettext('related', 'What do you want the title to be?');
        $js['address'] = 'index.php?module=related&action=postTitle';
        $js['answer'] = $related->title;
        $js['value_name'] = 'new_title';
        $js['link'] = Icon::show('edit');

        $edit = javascript('prompt', $js);

        $template['EDIT'] = $edit;

        if ($related->key_id != $current->key_id && !$related->isFriend($current)) {
            $template['ADD_LINK'] = '<a href="index.php?module=related&amp;action=add">'
            . dgettext('related', 'Add item') . '</a>';


            if (!empty($current->friends)) {
                $extra_friends = $current->listFriends();
                $template['EXTRA_INSTRUCTIONS'] = dgettext('related', 'This item is related to the following:');

                if (is_array($extra_friends)) {
                    foreach ($extra_friends as $key=>$friend_item){
                        $template['extra_list'][] = array('EXTRA_NAME'=>$friend_item);
                    }
                }
            }
        }

        $template['QUIT_LINK'] = '<a href="index.php?module=related&amp;action=quit">'
        . dgettext('related', 'Quit') . '</a>';

        Related_Action::setCurrent($current);

        $module = new PHPWS_Module($related->_key->module);
        $template['MODULE'] = $module->getProperName(TRUE);

        if (!empty($related->friends)) {
            $template['SAVE_LINK'] = '<a href="index.php?module=related&amp;action=save">'
            . dgettext('related', 'Save') . '</a>';

            $friends = $related->listFriends();

            if (is_array($friends)) {
                foreach ($friends as $key=>$friend_item){
                    $up = '<a href="index.php?module=related&amp;action=up&amp;pos=' . $key . '"><img src="' . PHPWS_SOURCE_HTTP . 'mod/related/img/up.png"/></a>';
                    $down = '<a href="index.php?module=related&amp;action=down&amp;pos=' . $key . '"><img src="' . PHPWS_SOURCE_HTTP . 'mod/related/img/down.png"/></a>';
                    $remove = '<a href="index.php?module=related&amp;action=remove&amp;pos=' . $key . '"><img src="' . PHPWS_SOURCE_HTTP . 'mod/related/img/remove.png"/></a>';

                    $template['friend_list'][] = array('FRIEND_NAME'=>$friend_item,
                                                       'UP'=>$up,
                                                       'DOWN'=>$down,
                                                       'REMOVE'=>$remove
                    );
                }
            }
        } else {
            $template['FRIEND_NAME'] = dgettext('related', 'View other items to add them to the list.');
        }
        return PHPWS_Template::process($template, 'related', 'edit.tpl');
    }


    public static function view(Related $related)
    {
        $friends = $related->listFriends();

        if (!is_array($friends)) {
            return $friends;
        }

        $tpl = new PHPWS_Template('related');
        $result = $tpl->setFile('view.tpl');

        $template['TITLE'] = $related->getUrl(TRUE);

        if (Current_User::allow('related')) {
            $linkvars = array('action' => 'edit',
                              'id'     => $related->id
            );
            $template['EDIT_LINK'] = PHPWS_Text::moduleLink(dgettext('related', 'Edit'), 'related', $linkvars);
        }

        foreach ($friends as $key=>$friend_item){
            $tpl->setCurrentBlock('friend_list');
            $tpl->setData(array('FRIEND_NAME'=>$friend_item));
            $tpl->parseCurrentBlock();
        }

        $tpl->setData($template);
        return $tpl->get();
    }

    public function newBank(Related $related)
    {
        unset($_SESSION['Related__Bank']);
        $_SESSION['Related_Bank'] = $related;
    }


    public static function setCurrent(Related $friend)
    {
        unset($_SESSION['Current__Friend']);
        $_SESSION['Current_Friend'] = $friend;
    }

    public static function setBank($related)
    {
        $_SESSION['Related_Bank'] = $related;
    }

    public static function getBank()
    {
        return $_SESSION['Related_Bank'];
    }


    public function isBanked()
    {
        if (isset($_SESSION['Related_Bank']) && $_SESSION['Related_Bank']->isBanked()) {
            return TRUE;
        } else {
            return FALSE;
        }
    }


    public static function start()
    {
        $related = new Related;
        $related->setKey($_GET['key']);
        $related->setBanked(TRUE);
        Related_Action::setBank($related);
        PHPWS_Core::reroute($related->getUrl());
    }

    public function quit()
    {
        $location = $_SESSION['Related_Bank']->getUrl();
        unset($_SESSION['Related_Bank']);
        PHPWS_Core::reroute($location);
    }

    public function add()
    {
        if (!isset($_SESSION['Related_Bank'])) {
            return dgettext('related', 'Bank not created.');
        }

        if (!isset($_SESSION['Current_Friend'])) {
            return dgettext('related', 'Friend not created.');
        }

        $related = & $_SESSION['Related_Bank'];
        $friend = & $_SESSION['Current_Friend'];

        $related->addFriend($friend);

        if (!empty($friend->friends)) {
            foreach ($friend->friends as $extra_friend) {
                $related->addFriend($extra_friend);
            }
        }

        PHPWS_Core::reroute($friend->getUrl());
    }

    public function up()
    {
        if (!isset($_SESSION['Related_Bank'])) {
            return dgettext('related', 'Bank not created.');
        }

        if (!isset($_REQUEST['pos'])) {
            return dgettext('related', 'Missing position.');
        }

        $_SESSION['Related_Bank']->moveFriendUp($_REQUEST['pos']);
        PHPWS_Core::reroute($_SESSION['Current_Friend']->getUrl());
    }

    public function down()
    {
        if (!isset($_SESSION['Related_Bank'])) {
            return dgettext('related', 'Bank not created.');
        }

        if (!isset($_REQUEST['pos'])) {
            return dgettext('related', 'Missing position.');
        }

        $_SESSION['Related_Bank']->moveFriendDown($_REQUEST['pos']);
        PHPWS_Core::reroute($_SESSION['Current_Friend']->getUrl());
    }

    public function remove()
    {
        if (!isset($_SESSION['Related_Bank'])) {
            return dgettext('related', 'Bank not created.');
        }

        if (!isset($_REQUEST['pos'])) {
            return dgettext('related', 'Missing position.');
        }

        $_SESSION['Related_Bank']->removeFriend($_REQUEST['pos']);
        PHPWS_Core::reroute($_SESSION['Current_Friend']->getUrl());
    }

    public function save()
    {
        if (!isset($_SESSION['Related_Bank'])) {
            return dgettext('related', 'Bank not created.');
        }

        $result = $_SESSION['Related_Bank']->save();

        if (PHPWS_Error::isError($result)) {
            PHPWS_Error::log($result);
            Layout::add(dgettext('related', 'The Related module encountered a database error.'));
            return;
        }

        Related_Action::quit();
    }

    public function changeForm()
    {
        $template['PAGE_TITLE'] = dgettext('related', 'Change Related Title');

        $related = Related_Action::getBank();

        $form = new PHPWS_Form;
        $form->add('module', 'hidden', 'related');
        $form->add('action', 'hidden', 'postTitle');
        $form->add('title', 'text', $related->title);
        $form->setSize('title', '30');
        $form->add('submit', 'submit', 'Update');

        $form->mergeTemplate($template);

        $template = $form->getTemplate();

        echo PHPWS_Template::process($template, 'related', 'change.tpl');
        exit();
    }

    public function postTitle()
    {
        if ($_REQUEST['new_title'] != 'null') {
            $related = & $_SESSION['Related_Bank'];
            $related->setTitle($_REQUEST['new_title']);
        }
        PHPWS_Core::reroute($related->getUrl());
    }

}

?>