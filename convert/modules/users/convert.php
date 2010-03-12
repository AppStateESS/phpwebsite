<?php

/**
 * Users conversion file
 *
 * Transfers users to new installation
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

// number of users to convert
define('BATCH_SET', 100);

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

    $form = new PHPWS_Form;
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
    if (!isset($_REQUEST['mode'])) {
        $content[] = _('You may convert two different ways.');
        $content[] = sprintf('<a href="%s">%s</a>',
        sprintf('index.php?command=convert&package=users&stage=2&convert_who=%s&mode=manual', $_REQUEST['convert_who']),
        _('Manual mode requires you to click through the conversion process.'));
        $content[] = sprintf('<a href="%s">%s</a>',
        sprintf('index.php?command=convert&package=users&stage=2&convert_who=%s&mode=auto', $_REQUEST['convert_who']),
        _('Automatic mode converts the data without your interaction.'));

        $content[] = ' ';
        $content[] = _('If you encounter problems, you should use manual mode.');
        $content[] = _('Conversion will begin as soon as you make your choice.');

        return implode('<br />', $content);
    } else {
        if ($_REQUEST['mode'] == 'auto') {
            $show_wait = TRUE;
        } else {
            $show_wait = FALSE;
        }

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

        $batch = new Batches('convert_users');

        $total_users = $db->count();
        if ($total_users < 1) {
            return _('No users to convert.');
        }

        $batch->setTotalItems($total_users);
        $batch->setBatchSet(BATCH_SET);

        if (isset($_REQUEST['reset_batch'])) {
            $batch->clear();
        }


        if (!$batch->load()) {
            $content[] = _('Batch previously run.');
        } else {
            if(!runBatch($db, $batch)) {
                $content[] = _('Some users caused conversion errors.');
            }
        }

        $percent = $batch->percentDone();
        $content[] = Convert::getGraph($percent, $show_wait);
        $batch->completeBatch();

        if (!$batch->isFinished()) {
            if ($_REQUEST['mode'] == 'manual') {
                $content[] =  $batch->continueLink();
            } else {
                Convert::forward($batch->getAddress());
            }
        } else {
            // delete?
            unset($_SESSION['users_convert_init']);
            createSeqTable();
            $batch->clear();
            Convert::addConvert('users');
            $content[] =  _('All done!');
            $content[] = _('Please note that the user with an index of 1 was ignored by the conversion.');
            $content[] = _('If the first user on your old site is not you, then you will need to recreate them.');
            $content[] = '<a href="index.php">' . _('Go back to main menu.') . '</a>';
        }

        return implode('<br />', $content);
    }
}

function runBatch(&$db, &$batch)
{
    $start = $batch->getStart();
    $limit = $batch->getLimit();
    $db->setLimit($limit, $start);
    $result = $db->select();
    $db->disconnect();
    Convert::siteDB();

    $username = strtolower(Current_User::getUsername());

    initialize();
    if (empty($_SESSION['users_convert_init']) || empty($result)) {
        return false;
    } else {
        foreach ($result as $oldUser) {
            if ($oldUser['user_id'] == 1 ||
            strtolower($oldUser['username']) == $username) {
                continue;
            }
            $result = convertUser($oldUser);
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
                $errors = TRUE;
            }
        }
    }

    if (isset($errors)) {
        return FALSE;
    } else {
        return TRUE;
    }
}

function convertUser($oldUser)
{
    $db = new PHPWS_DB('users');
    $val['id']           = $oldUser['user_id'];
    $val['username']     = strtolower($oldUser['username']);
    $val['display_name'] = $oldUser['username'];
    $val['created']      = $val['last_logged']  = $oldUser['last_on'];
    $val['email']        = $oldUser['email'];
    $val['deity']        = $oldUser['deity'];
    $val['updated']      = mktime();
    $val['log_count']    = $oldUser['log_sess'];
    $val['authorize']    = $_SESSION['users_convert_init'];
    $val['active']       = 1;
    $val['approved']     = 1;
    $db->addValue($val);

    $result = $db->insert(FALSE);
    if (PEAR::isError($result)) {
        return $result;
    }

    $db->reset();
    $db->setTable('users_groups');
    $db->addValue('active', 1);
    $db->addValue('name', $val['username']);
    $db->addValue('user_id', $val['id']);

    $result = $db->insert();
    if (PEAR::isError($result)) {
        return $result;
    }

    $convert = new PHPWS_DB('users_conversion');
    $convert->addValue('username', $val['username']);
    $convert->addValue('password', $oldUser['password']);
    return $convert->insert(FALSE);
}

function initialize()
{
    if (isset($_SESSION['users_convert_init'])) {
        return;
    }

    if (!PHPWS_DB::isTable('users_conversion')) {
        $db = new PHPWS_DB('users_conversion');
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
    } else {
        $db = new PHPWS_DB('users_auth_scripts');
        $db->addWhere('filename', 'convert.php');
        $db->addColumn('id');
        $id = $db->select('one');
        if (PEAR::isError($id)) {
            PHPWS_Error::log($id);
            PHPWS_Core::errorPage();
        }
        if (empty($id)) {
            $db->reset();
            $db->addValue('display_name', 'Convert');
            $db->addValue('filename', 'convert.php');
            $id = $db->insert();
            if (PEAR::isError($id)) {
                PHPWS_Error::log($id);
                PHPWS_Core::errorPage();
            }
        }
        $_SESSION['users_convert_init'] = $id;
    }
}

function createSeqTable()
{
    $db = new PHPWS_DB('users');
    $result = $db->updateSequenceTable();
}


?>