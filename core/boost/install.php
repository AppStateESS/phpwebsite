<?php

function core_install()
{
    $db = \Database::getDB();
    $tbl = $db->addTable('settings');
    $tbl->createPrimaryIndexId();        
}