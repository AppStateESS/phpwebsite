<?php

function core_install()
{
    $db = \phpws2\Database::getDB();
    $tbl = $db->addTable('settings');
    $tbl->createPrimaryIndexId();        
}