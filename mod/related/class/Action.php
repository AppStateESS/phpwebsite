<?php

/**
 * Administrative options within the related module
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

class Related_Action {

    function create(&$related)
    {
        $template['TITLE_LBL'] = _('Title');
        $template['MODULE_LBL'] = _('Module');

        $instructions[] = _('Currently, nothing is associated with this item.');
        $instructions[] = _('If you want to add related information to this item, click the "Build Related" link.');

        $template['INSTRUCTIONS'] = implode('<br />', $instructions);

        $vars['action'] = 'start';
        $vars['key'] = $related->key_id;

        $template['LINK'] = PHPWS_Text::secureLink(_('Build Related'), 'related', $vars);
        $template['TITLE'] = $related->getUrl(TRUE);

        $module = & new PHPWS_Module($related->_key->module);
        $template['MODULE'] = $module->getProperName(TRUE);

        return PHPWS_Template::process($template, 'related', 'create.tpl');
    }

    function edit(&$current)
    {
        PHPWS_Core::initCoreClass('Module.php');

        $related = & Related_Action::getBank();
        $template['TITLE_LBL'] = _('Title');
        $template['MODULE_LBL'] = _('Module');
        $template['TITLE'] = $related->getUrl(TRUE);

        $id = $related->id;

        $js['question'] = _('What do you want the title to be?');
        $js['address'] = 'index.php?module=related&action=postTitle';
        $js['answer'] = $related->title;
        $js['value_name'] = 'new_title';
        $js['link'] = '<img src="images/mod/related/edit.png"/>';

        $edit = javascript('prompt', $js);

        $template['EDIT'] = $edit;

        if ($related->key_id != $current->key_id && !$related->isFriend($current)) {
            $template['ADD_LINK'] = '<a href="index.php?module=related&amp;action=add">'
                . _('Add item') . '</a>';


            if (!empty($current->friends)) {
                $extra_friends = $current->listFriends();
                $template['EXTRA_INSTRUCTIONS'] = _('This item is related to the following:');
        
                if (is_array($extra_friends)) {
                    foreach ($extra_friends as $key=>$friend_item){
                        $template['extra_list'][] = array('EXTRA_NAME'=>$friend_item);
                    }
                }
            }
        }

        $template['QUIT_LINK'] = '<a href="index.php?module=related&amp;action=quit">'
            . _('Quit') . '</a>';

        Related_Action::setCurrent($current);

        $module = & new PHPWS_Module($related->_key->module);
        $template['MODULE'] = $module->getProperName(TRUE);

        if (!empty($related->friends)) {
            $template['SAVE_LINK'] = '<a href="index.php?module=related&amp;action=save">'
                . _('Save') . '</a>';

            $friends = $related->listFriends();

            if (is_array($friends)) {
                foreach ($friends as $key=>$friend_item){
                    $up = '<a href="index.php?module=related&amp;action=up&amp;pos=' . $key . '"><img src="images/mod/related/up.png"/></a>';
                    $down = '<a href="index.php?module=related&amp;action=down&amp;pos=' . $key . '"><img src="images/mod/related/down.png"/></a>';
                    $remove = '<a href="index.php?module=related&amp;action=remove&amp;pos=' . $key . '"><img src="images/mod/related/remove.png"/></a>';

                    $template['friend_list'][] = array('FRIEND_NAME'=>$friend_item,
                                                       'UP'=>$up,
                                                       'DOWN'=>$down,
                                                       'REMOVE'=>$remove
                                                       );
                }
            }
        } else {
            $template['FRIEND_NAME'] = _('View other items to add them to the list.');
        }

        return PHPWS_Template::process($template, 'related', 'edit.tpl');
    }


    function view(&$related)
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
            $template['EDIT_LINK'] = PHPWS_Text::moduleLink(_('Edit'), 'related', $linkvars);
        }

        foreach ($friends as $key=>$friend_item){
            $tpl->setCurrentBlock('friend_list');
            $tpl->setData(array('FRIEND_NAME'=>$friend_item));
            $tpl->parseCurrentBlock();
        }

        $tpl->setData($template);
        return $tpl->get();
    }

    function newBank(&$related)
    {
        unset($_SESSION['Related__Bank']);
        $_SESSION['Related_Bank'] = $related;
    }


    function setCurrent(&$friend)
    {
        unset($_SESSION['Current__Friend']);
        $_SESSION['Current_Friend'] = $friend;
    }

    function setBank($related)
    {
        $_SESSION['Related_Bank'] = $related;
    }

    function &getBank()
    {
        return $_SESSION['Related_Bank'];
    }


    function isBanked()
    {
        if (isset($_SESSION['Related_Bank']) && $_SESSION['Related_Bank']->isBanked()) {
            return TRUE;
        } else {
            return FALSE;
        }
    }


    function start()
    {
        $related = & new Related;
        $related->setKey($_REQUEST['key']);
        $related->setBanked(TRUE);
        Related_Action::setBank($related);
        PHPWS_Core::reroute($related->getUrl());
    }

    function quit()
    {
        $location = $_SESSION['Related_Bank']->getUrl();
        unset($_SESSION['Related_Bank']);
        PHPWS_Core::reroute($location);
    }

    function add()
    {
        if (!isset($_SESSION['Related_Bank'])) {
            return _('Bank not created.');
        }

        if (!isset($_SESSION['Current_Friend'])) {
            return _('Friend not created.');
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

    function up()
    {
        if (!isset($_SESSION['Related_Bank'])) {
            return _('Bank not created.');
        }

        if (!isset($_REQUEST['pos'])) {
            return _('Missing position.');
        }

        $_SESSION['Related_Bank']->moveFriendUp($_REQUEST['pos']);
        PHPWS_Core::reroute($_SESSION['Current_Friend']->getUrl());
    }

    function down()
    {
        if (!isset($_SESSION['Related_Bank'])) {
            return _('Bank not created.');
        }

        if (!isset($_REQUEST['pos'])) {
            return _('Missing position.');
        }

        $_SESSION['Related_Bank']->moveFriendDown($_REQUEST['pos']);
        PHPWS_Core::reroute($_SESSION['Current_Friend']->getUrl());
    }

    function remove()
    {
        if (!isset($_SESSION['Related_Bank'])) {
            return _('Bank not created.');
        }

        if (!isset($_REQUEST['pos'])) {
            return _('Missing position.');
        }

        $_SESSION['Related_Bank']->removeFriend($_REQUEST['pos']);
        PHPWS_Core::reroute($_SESSION['Current_Friend']->getUrl());
    }

    function save()
    {
        if (!isset($_SESSION['Related_Bank'])) {
            return _('Bank not created.');
        }

        $result = $_SESSION['Related_Bank']->save();

        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            Layout::add(_('The Related module encountered a database error.'));
            return;
        }
    
        Related_Action::quit();
    }

    function changeForm()
    {
        $template['PAGE_TITLE'] = _('Change Related Title');

        $related = Related_Action::getBank();

        $form = & new PHPWS_Form;
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

    function postTitle()
    {
        if ($_REQUEST['new_title'] != 'null') {
            $related = & $_SESSION['Related_Bank'];
            $related->setTitle($_REQUEST['new_title']);
        }
        PHPWS_Core::reroute($related->getUrl());
    }

}

?>