<?php

function convert()
{

    $db = Convert::getSourceDB('mod_fatcat_categories');
    //    unset($_SESSION['Batches']);
    //    test($_SESSION['Batches']);

    $batch = getBatch($db);

    if (isset($_REQUEST['reset_batch'])) {
        $batch->clear();
    }

    if (!$batch->load()) {
        $content[] = 'Batch previously run.';
    } else {
        runBatch($db, $batch);
    }

    $content[] = sprintf('%s&#37; done<br>', $batch->percentDone());

    $batch->completeBatch();

    
    if (!$batch->isFinished()) {
        $content[] =  $batch->continueLink();
    } else {
        $batch->clear();
        $content[] =  _('All done!');
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

function &getBatch(&$db)
{
    $batch = & new Batches('convert_fatcat');
    $total_categories = $db->count();
    $batch->setTotalItems($total_categories);
    $batch->setBatchSet(10);
    return $batch;
}

?>