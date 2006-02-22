<?php

  /**
   * Users conversion file
   *
   * Transfers users to new installation
   *
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function convert()
{
    $content = array();

    if (!isset($_REQUEST['stage'])) {
        $content[] = startOptions();
    } else {
        if ($_REQUEST['stage'] == 1) {
            switch ($_POST['convert_who']) {
            case 'all':
                $link = _('Continue: Convert everyone.');
                break;

            case 'month':
                $link = _('Continue: Only those logged on within 6 months.');
                break;

            case 'year':
                $link = _('Continue: Only those logged on within the last year.');
                break;
            }

            $content[] = sprintf('<a href="index.php?command=convert&amp;package=users&amp;stage=2&amp;convert_who=%s">%s</a>',
                                 $_POST['convert_who'], $link);
            $content[] = _('Return to main page if you change your mind. Click the above link to continue.');
        } elseif ($_REQUEST['stage'] == 2) {
            $content[] = beginConverting();
        }
    }


    return implode('<br />', $content);
}


function startOptions()
{
    $content[] = _('You may choose to ignore users who haven\'t logged in for a while.');

    $convert_list['all'] = _('Convert everyone.');
    $convert_list['month'] = _('Only those logged on within 6 months.');
    $convert_list['year'] = _('Only those logged on within the last year.');

    $form = & new PHPWS_Form;
    $form->addHidden('command', 'convert');
    $form->addHidden('package', 'users');
    $form->addHidden('stage', 1);
    $form->addSelect('convert_who', $convert_list);
    $form->addSubmit(_('Continue'));
    $tpl = $form->getTemplate();
    $content[] = implode("\n ", $tpl);
    return implode('<br />', $content);
}

function beginConverting()
{
    $db = Convert::getSourceDB('mod_users');

    switch ($_REQUEST['convert_who']) {
    case 'month':
        $sixmonths = mktime(0,0,0, date('m')-6, date('d'), date('Y')); 
        $db->addWhere('last_on', $sixmonths, '>=');
        break;

    case 'year':
        $year = mktime(0,0,0, date('m'), date('d'), date('Y')-1); 
        $db->addWhere('last_on', $year, '>=');
        break;
    }

    $batch = & new Batches('convert_users');

    $total_users = $db->count();
    if ($total_users < 1) {
        return _('No users to convert.');
    }

    $batch->setTotalItems($total_users);
    $batch->setBatchSet(5);

    if (isset($_REQUEST['reset_batch'])) {
        $batch->clear();
    }


    if (!$batch->load()) {
        $content[] = _('Batch previously run.');
    } else {
        $result = runBatch($db, $batch);
    }

    $content[] = sprintf('%s&#37; done<br>', $batch->percentDone());

    $batch->completeBatch();

    if (!$batch->isFinished()) {
        $content[] =  $batch->continueLink();
    } else {
        // delete?
        unset($_SESSION['users_convert_init']);
        //        createSeqTable();
        $batch->clear();
        //        Convert::addConvert('users');
        $content[] =  _('All done!');
        $content[] = _('Please note that the user with an index of 1 was ignored by the conversion.');
        $content[] = _('If the first user on your old site is not you, then you will need to recreate them.');
        $content[] = '<a href="index.php">' . _('Go back to main menu.') . '</a>';
    }
    
    return implode('<br />', $content);
}

function runBatch(&$db, &$batch)
{
    $start = $batch->getStart();
    $limit = $batch->getLimit();
    $db->setLimit($start, $limit);
    $result = $db->select();
    $db->disconnect();

    initialize();
    if (empty($result)) {
        return NULL;
    } else {
        foreach ($result as $oldUser) {
            if ($oldUser['user_id'] == 1) {
                continue;
            }
            $result = convertUser($oldUser);
        }
    }

    return TRUE;
}

function convertUser($oldUser) {
    test($oldUser);
    $db = & new PHPWS_DB('mod_users');

    $val['id']          = $oldUser['user_id'];
    $val['username']    = $oldUser['username'];
    $val['created'] = 
    $val['last_logged'] = $oldUser['last_on'];
    $val['email']       = $oldUser['email'];
    $val['deity']       = $oldUser['deity'];
    $val['updated']     = mktime();
    $val['log_count']   = $oldUser['log_sess'];
    $val['authorize']   = $_SESSION['users_convert_init'];
    test($val);


}

function initialize()
{
    if (isset($_SESSION['users_convert_init'])) {
        return;
    }
    $db = & new PHPWS_DB('users_conversion');
    if (!$db->isTable('users_conversion')) {
        $db->addValue('username', 'varchar(50) NOT NULL');
        $db->addValue('password', 'char(32) NOT NULL');
        $db->createTable();
        $db->reset();
        $db->setTable('users_auth_scripts');
        $db->addValue('display_name', 'Convert');
        $db->addValue('filename', 'convert.php');
        $id = $db->insert();
        if (PEAR::isError($id)) {
            PHPWS_Error::log($id);
            PHPWS_Core::errorPage();
        }
        $_SESSION['users_convert_init'] = $id;
    }
}

?>