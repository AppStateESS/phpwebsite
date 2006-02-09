<?php

  /**
   * Category conversion file
   *
   * Transfers fatcat stuff to categories
   *
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function convert()
{

    if ( (!Convert::isConverted('webpage') || !Convert::isConverted('blog')) && !isset($_GET['ignore'])) {
        $content[] = _('Any content modules using FatCat should be converted BEFORE continuing.');
        $content[] = sprintf('<a href="index.php?command=convert&amp;package=menu&amp;ignore=1">%s</a>', _('Click to continue anyway.'));
        $content[] = _('Otherwise, click on the "Main page" link above.');
        return implode('<br />', $content);
    }

    if (!Convert::isConverted('categories')) {
        return convertCategories();
    } elseif (!Convert::isConverted('category_items')) {
        return convertItems();
    } else {
        return _('Categories has already been converted.');
    }



}

function convertCategories()
{
    $db = Convert::getSourceDB('mod_fatcat_categories');
    $batch = & new Batches('convert_categories');
    $total_categories = $db->count();
    $batch->setTotalItems($total_categories);
    $batch->setBatchSet(30);

    if (isset($_REQUEST['reset_batch'])) {
        $batch->clear();
    }

    if (!$batch->load()) {
        $content[] = 'Batch previously run.';
    } else {
        runCatBatch($db, $batch);
    }

    $content[] = sprintf('%s&#37; done<br>', $batch->percentDone());

    $batch->completeBatch();

    
    if (!$batch->isFinished()) {
        $content[] =  $batch->continueLink();
    } else {
        $batch->clear();
        Convert::addConvert('categories');
        $content[] =  _('Finished converting categories!');
        $content[] = '<a href="index.php?command=convert&amp;package=categories">' . _('Continue to convert category elements.') . '</a>';
    }
    
    return implode('<br />', $content);
}

function convertItems()
{
    $db = Convert::getSourceDB('mod_fatcat_elements');

    /**
     * Going to ignore calendar for now. When calendar is finished
     * I'll put it back in.
     */
    $db->addWhere('module_title', 'calendar', '!=');

    $batch = & new Batches('convert_category_items');
    $total_items = $db->count();

    $batch->setTotalItems($total_items);
    $batch->setBatchSet(30);

    if (isset($_REQUEST['reset_batch'])) {
        $batch->clear();
    }

    if (!$batch->load()) {
        $content[] = 'Batch previously run.';
    } else {
        runCatItemBatch($db, $batch);
    }

    $content[] = sprintf('%s&#37; done<br>', $batch->percentDone());

    $batch->completeBatch();

    
    if (!$batch->isFinished()) {
        $content[] =  $batch->continueLink();
    } else {
        $batch->clear();
        Convert::addConvert('categories');
        $content[] =  _('Finished converting category items!');
        $content[] = '<a href="index.php">' . _('Return to the main page.') . '</a>';
    }
    
    return implode('<br />', $content);

}

function runCatBatch(&$db, &$batch)
{
    $start = $batch->getStart();
    $limit = $batch->getLimit();
    $db->setLimit($start, $limit);
    $result = $db->select();
    $db->disconnect();

    $newdb = & new PHPWS_DB('categories');

    if (empty($result)) {
        return NULL;
    } else {
        foreach ($result as $oldCat) {
            $val['id']          = $oldCat['cat_id'];
            $val['title']       = $oldCat['title'];
            $val['description'] = $oldCat['description'];
            $val['parent']      = $oldCat['parent'];
            $newdb->addValue($val);
            $result = $newdb->insert(FALSE);
            $newdb->reset();
        }
    }

}

function runCatItemBatch(&$db, &$batch)
{
    $start = $batch->getStart();
    $limit = $batch->getLimit();
    $db->setLimit($start, $limit);
    $result = $db->select();
    $db->disconnect();

    $item_db = & new PHPWS_DB('category_items');
    $key_db = & new PHPWS_DB('phpws_key');

    if (empty($result)) {
        return NULL;
    } else {
        foreach ($result as $item) {
            $module = $item_name = $item_id = NULL;

            switch ($item['module_title']) {
            case 'announce':
                $module = 'blog';
                $item_name = 'entry';
                break;

            case 'calendar':

                break;

            case 'photoalbum':

                break;

            case 'pagemaster':
                $module = 'webpage';
                $item_name = 'volume';
                break;

            case 'documents':
                break;

            case 'phatform':
                break;
            } // end item switch

            if (empty($module)) {
                continue;
            }

            $item_id = $item['module_id'];
            $key_db->addWhere('module', $module);
            $key_db->addWhere('item_name', $item_name);
            $key_db->addWhere('item_id', $item_id);
            $key_db->addColumn('id');
            $key_id = $key_db->select('one');
            $key_db->reset();
            if (empty($key_id)) {
                continue;
            } else {
                $item_db->addValue('key_id', $key_id);
                $item_db->addValue('cat_id', $item['cat_id']);
                $item_db->insert();
                $item_db->reset();
            }
        }
    }

}

?>