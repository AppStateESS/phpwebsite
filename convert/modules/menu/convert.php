<?php

  /**
   * Conversion file for Menu module
   *
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

/**
 * Put your web address here to strip from links
 * example somesite.com
 * Don't add http://
 */
define('RELATIVE_URL', 'op.appstate.edu');

function convert()
{
    if (Convert::isConverted('pagesmith')) {
        $GLOBALS['Convert_mod'] = 'pagesmith';
    } elseif (Convert::isConverted('webpage')) {
        $GLOBALS['Convert_mod'] = 'webpage';
    }


    if (!isset($GLOBALS['Convert_mod']) && !isset($_GET['ignore'])) {
        $content[] = _('Any content modules using Menu Manager should be converted BEFORE continuing..');
        $content[] = sprintf('<a href="index.php?command=convert&amp;package=menu&amp;ignore=1">%s</a>', _('Click to continue anyway.'));
        $content[] = _('Otherwise, click on the "Main page" link above.');
        return implode('<br />', $content);
    }

    $mod_list = PHPWS_Core::installModList();

    if (!in_array('menu', $mod_list)) {
        return _('Menu is not installed.');
    }


    if (!Convert::isConverted('menus')) {
        return convertMenu();
    } elseif (!Convert::isConverted('menu_links')) {
        return convertLinks();
    } else {
        return _('Menu has already been converted.');
    }
}


function convertMenu()
{
    $content[] = _('Convert menus');
    $db = Convert::getSourceDB('mod_menuman_menus');
    $result = $db->select();
    if (empty($result)) {
        return _('No menus found.');
    }

    $db->disconnect();
    Convert::siteDB();

    $newdb = new PHPWS_DB('menus');
    $newdb->truncateTable();
    $newdb->dropTable('menus_seq', true, false);

    foreach ($result as $menu) {
        $val['id']         = $menu['menu_id'];
        $val['title']      = utf8_encode($menu['menu_title']);
        $val['template']   = 'basic';
        $val['restricted'] = 0;
        $val['pin_all']    = 1;
        $newdb->addValue($val);
        $result = $newdb->insert(false);
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $errors[] = $val['title'];
        }
        $newdb->reset();
    }

    if (isset($errors)) {
        $content[] = _('Some menus did not convert over properly. Please see logs.');
    } else {
        Convert::addConvert('menus');
        PHPWS_Core::killSession('Menu_New_Id');
        PHPWS_Core::killSession('');
        $content[] = _('Menu conversion finished.');
        $content[] = sprintf('<a href="index.php?command=convert&amp;package=menu">%s</a>',
                             _('Continue to convert menu links.'));
    }

    return implode('<br />', $content);
}

function convertLinks()
{
    $content[] = _('Convert menu links');

    $db = Convert::getSourceDB('mod_menuman_items');

    $batch = & new Batches('convert_menu_links');
    $total_links = $db->count();
    if ($total_links < 1) {
        return _('No menu links to convert.');
    }

    $batch->setTotalItems($total_links);
    $batch->setBatchSet(10);

    if (isset($_REQUEST['reset_batch'])) {
        $batch->clear();
    }

    if (!$batch->load()) {
        $content[] = _('Batch previously run.');
    } else {
        $result = linkBatch($db, $batch);
    }
    $content[] = sprintf('%s&#37; done<br>', $batch->percentDone());

    $batch->completeBatch();
    
    if (!$batch->isFinished()) {
        $content[] =  $batch->continueLink();
    } else {
        resetLinkOrders();

        createSeqTables();
        $batch->clear();
        PHPWS_Core::killSession('Menu_New_Id');
        $content[] =  _('Finished converting links.');
        $content[] = '<a href="index.php">' . _('Go back to main menu.') . '</a>';
    }
    
    return implode('<br />', $content);
}

function resetLinkOrders()
{
    $db = new PHPWS_DB('menu_links');
    $db->setIndexBy('parent');
    $db->addOrder('link_order');
    $result = $db->select();

    $db->reset();

    foreach ($result as $parent_id => $links) {
        $count = 1;
        if (isset($links[0])) {
            foreach ($links as $link) {
                $db->addValue('link_order', $count);
                $db->addWhere('id', $link['id']);
                $db->update();
                $db->reset();
                $count++;
            }
        } else {
            $db->addValue('link_order', 1);
            $db->addWhere('id', $links['id']);
            $db->update();
            $db->reset();
        }
    }
}

function linkBatch($db, $batch)
{
    $start = $batch->getStart();
    $limit = $batch->getLimit();
    $db->setLimit($limit, $start);
    $result = $db->select();
    $db->disconnect();
    Convert::siteDB();

    if (empty($result)) {
        return NULL;
    } else {
        foreach ($result as $link) {
            $link_result = convertLink($link);
            if (PEAR::isError($link_result)) {
                PHPWS_Error::log($link_result);
            }
        }
    }

    return TRUE;
}

function convertLink($link) {
    $val['id']         = $link['menu_item_id'];
    $val['menu_id']    = $link['menu_id'];

    if ($link['menu_item_pid'] != $link['menu_item_id']) {
        $val['parent'] = $link['menu_item_pid'];
    } else {
        $val['parent'] = 0;
    }
    $val['title']      = $link['menu_item_title'];
    $val['url']        = processUrl($link['menu_item_url']);

    if (isset($GLOBALS['Convert_mod'])) {
        $key_id = 0;
        $mod = $GLOBALS['Convert_mod'];
        $page_id = (int)preg_replace('/.*=(\d+)$/', '\\1', $val['url']);

        if ($page_id) {
            $db = new PHPWS_DB('phpws_key');
            $db->addColumn('id');
            $db->addWhere('item_id', $page_id);
            $db->addWhere('module', $mod);
            $key_id = $db->select('one');

            if (!PHPWS_Error::logIfError($key_id) && $key_id) {
                $val['key_id'] = $key_id;
            }
        }
    }

    $val['link_order'] = $link['menu_item_order'];

    $db = new PHPWS_DB('menu_links');
    $db->addValue($val);
    return $db->insert(false);
}

function processUrl($link)
{
    if (isset($GLOBALS['Convert_mod'])) {
        $mod = $GLOBALS['Convert_mod']; 
    } else {
        $mod = null;
    }

    $relative = RELATIVE_URL;

    if (!empty($relative)) {
        $url = preg_quote($relative);
        $link = preg_replace("/http(s)?:\/\/(www\.)?$relative\//i", './', $link);
    }

    $link = str_replace('&amp;', '&', $link);
    if ($mod) {
        $link = preg_replace('/index.php\?module=pagemaster&page_user_op=view_page&page_id=(\d+).*/i', 'index.php?module=' . $mod . '&id=\\1', $link);
    }

    return $link;
}

function createSeqTables()
{
    $db1 = new PHPWS_DB('menu_links');
    $result = $db1->updateSequenceTable();

    $db2 = new PHPWS_DB('menus');
    $result = $db2->updateSequenceTable();

}

?>