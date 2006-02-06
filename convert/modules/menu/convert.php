<?php

function convert()
{
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

    $newdb = & new PHPWS_DB('menus');

    foreach ($result as $menu) {
        $val['id']         = $menu['menu_id'];
        $val['title']      = $menu['menu_title'];
        $val['template']   = 'basic.tpl';
        $val['restricted'] = 0;
        $val['pin_all']    = 1;
        $newdb->addValue($val);
        $result = $newdb->insert(FALSE);
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
        $batch->clear();
        $content[] =  _('Finished converting links.');
        $content[] = '<a href="index.php">' . _('Go back to main menu.') . '</a>';
    }
    
    return implode('<br />', $content);
}

function linkBatch($db, $batch)
{
    $start = $batch->getStart();
    $limit = $batch->getLimit();
    $db->setLimit($start, $limit);
    $result = $db->select();
    $db->disconnect();

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
    $db = & new PHPWS_DB('menu_links');

    $val['id']         = $link['menu_item_id'];
    $val['menu_id']    = $link['menu_id'];
    if ($link['menu_item_pid'] != $val['id']) {
        $val['parent'] = $link['menu_item_pid'];
    } else {
        $val['parent'] = 0;
    }
    $val['title']      = $link['menu_item_title'];
    $val['url']        = $link['menu_item_url'];
    $val['link_order'] = $link['menu_item_order'];

    $db->addValue($val);
    return $db->insert(FALSE);
}

?>