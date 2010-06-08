<?php

/**
 * Wiki for phpWebSite
 *
 * See docs/CREDITS for copyright information
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @author      Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
 * $Id: convert.php,v 1.5 2007/05/28 19:00:17 blindman1344 Exp $
 */

/*
 * This should be set to the image directory of your 0.10.x install. Set and
 * uncomment this before attempting conversion!  Make sure to include the
 * trailing slash.
 */
//define('OLD_WIKI_IMAGES', '/path/to/old/images/wiki/');

function convert()
{
    if (Convert::isConverted('wiki'))
    {
        return dgettext('wiki', 'Wiki pages have already been converted.');
    }

    if (!defined('OLD_WIKI_IMAGES'))
    {
        return dgettext('wiki', 'OLD_WIKI_IMAGES in Wiki conversion script needs to be set BEFORE continuing.');
    }

    $mod_list = \core\Core::installModList();

    if (!in_array('wiki', $mod_list))
    {
        return dgettext('wiki', 'Wiki module is not installed.');
    }

    if (!isset($_SESSION['wiki_convert_step']))
    {
        $_SESSION['wiki_convert_step'] = 1;
    }

    switch ($_SESSION['wiki_convert_step'])
    {
        case 1:
            return step1();
            break;

        case 2:
            return step2();
            break;

        case 3:
            return step3();
            break;
    }
}

/* CONVERT WIKI PAGES */
function step1()
{
    $db = Convert::getSourceDB('mod_wiki_pages');

    $batch = new Batches('convert_wiki_pages');
    $total = $db->count();
    if ($total < 1)
    {
        $batch->clear();
        $_SESSION['wiki_convert_step'] = 2;
        Convert::forward(core\Core::getHomeHttp() . 'index.php?command=convert&package=wiki');
        return dgettext('wiki', 'No wiki pages to convert.');
    }
    $batch->setTotalItems($total);
    $batch->setBatchSet(5);

    if (isset($_REQUEST['reset_batch']))
    {
        $batch->clear();
    }

    $content[] =  dgettext('wiki', 'Converting wiki pages...');

    if (!$batch->load())
    {
        $content[] = dgettext('wiki', 'Batch previously run.');
    }
    else
    {
        $result = runBatch($db, $batch);
        if (is_array($result))
        {
            $content[] = dgettext('wiki', 'Some errors occurred when trying to convert the following pages:');
            $content[] = '<ul><li>' . implode('</li><li>', $result) . '</li></ul>';
            return implode('<br />', $content);
        }
    }

    $percent = $batch->percentDone();
    $content[] = Convert::getGraph($percent);

    $batch->completeBatch();

    if (!$batch->isFinished())
    {
        Convert::forward($batch->getAddress());
    }
    else
    {
        $batch->clear();
        $_SESSION['wiki_convert_step'] = 2;
        Convert::forward(core\Core::getHomeHttp() . 'index.php?command=convert&package=wiki');
    }

    return implode('<br />', $content);
}

/* CONVERT INTERWIKI LINKS */
function step2()
{
    $db = Convert::getSourceDB('mod_wiki_interwiki');

    $batch = new Batches('convert_wiki_interwiki');
    $total = $db->count();
    if ($total < 1)
    {
        $batch->clear();
        $_SESSION['wiki_convert_step'] = 3;
        Convert::forward(core\Core::getHomeHttp() . 'index.php?command=convert&package=wiki');
        return dgettext('wiki', 'No interwiki links to convert.');
    }
    $batch->setTotalItems($total);
    $batch->setBatchSet(5);

    if (isset($_REQUEST['reset_batch']))
    {
        $batch->clear();
    }

    $content[] =  dgettext('wiki', 'Converting interwiki links...');

    if (!$batch->load())
    {
        $content[] = dgettext('wiki', 'Batch previously run.');
    }
    else
    {
        $result = runBatch($db, $batch);
        if (is_array($result))
        {
            $content[] = dgettext('wiki', 'Some errors occurred when trying to convert the following interwiki links:');
            $content[] = '<ul><li>' . implode('</li><li>', $result) . '</li></ul>';
            return implode('<br />', $content);
        }
    }

    $percent = $batch->percentDone();
    $content[] = Convert::getGraph($percent);

    $batch->completeBatch();

    if (!$batch->isFinished())
    {
        Convert::forward($batch->getAddress());
    }
    else
    {
        $batch->clear();
        $_SESSION['wiki_convert_step'] = 3;
        Convert::forward(core\Core::getHomeHttp() . 'index.php?command=convert&package=wiki');
    }

    return implode('<br />', $content);
}

/* CONVERT WIKI IMAGES */
function step3()
{
    $db = Convert::getSourceDB('mod_wiki_images');

    $batch = new Batches('convert_wiki_images');
    $total = $db->count();
    if ($total < 1)
    {
        $batch->clear();
        Convert::addConvert('wiki');
        return dgettext('wiki', 'No wiki images to convert.  Module conversion complete!');
    }
    $batch->setTotalItems($total);
    $batch->setBatchSet(5);

    if (isset($_REQUEST['reset_batch']))
    {
        $batch->clear();
    }

    $content[] =  dgettext('wiki', 'Converting wiki images...');

    if (!$batch->load())
    {
        $content[] = dgettext('wiki', 'Batch previously run.');
    }
    else
    {
        $result = runBatch($db, $batch);
        if (is_array($result))
        {
            $content[] = dgettext('wiki', 'Some errors occurred when trying to convert the following images:');
            $content[] = '<ul><li>' . implode('</li><li>', $result) . '</li></ul>';
            return implode('<br />', $content);
        }
    }

    $percent = $batch->percentDone();
    $content[] = Convert::getGraph($percent);

    $batch->completeBatch();

    if (!$batch->isFinished())
    {
        Convert::forward($batch->getAddress());
    }
    else
    {
        $batch->clear();
        Convert::addConvert('wiki');
        $content[] =  dgettext('wiki', 'Module conversion complete!');
        $content[] = '<a href="index.php">' . dgettext('wiki', 'Go back to main menu.') . '</a>';
    }

    return implode('<br />', $content);
}

function runBatch(&$db, &$batch)
{
    $db->setLimit($batch->getStart(), $batch->getLimit());
    $result = $db->select();
    $db->disconnect();

    if (empty($result))
    {
        return NULL;
    }
    else
    {
        foreach ($result as $oldItem)
        {
            if ($_SESSION['wiki_convert_step'] == 1)
            {
                if (!convertPage($oldItem))
                {
                    $errors[] = $oldItem['label'];
                }
            }
            else if ($_SESSION['wiki_convert_step'] == 2)
            {
                if (!convertInterwiki($oldItem))
                {
                    $errors[] = $oldItem['label'];
                }
            }
            else
            {
                if (!convertImage($oldItem))
                {
                    $errors[] = $oldItem['filename'];
                }
            }
        }
    }
    if (isset($errors))
    {
        return $errors;
    }
    else
    {
        return TRUE;
    }
}

function convertPage($page)
{
    \core\Core::initModClass('wiki', 'WikiManager.php');
    \core\Core::initModClass('wiki', 'WikiPage.php');
    \core\Core::initModClass('version', 'Version.php');
    \core\Core::initModClass('search', 'Search.php');

    $newpage = new WikiPage($page['label']);
    $newpage->setPagetext($page['pagetext']);
    $newpage->setOwnerId(Current_User::getId());
    $newpage->setEditorId(Current_User::getId());
    $newpage->setCreated($page['created']);
    $newpage->setUpdated(mktime());
    $newpage->setComment(dgettext('wiki', 'Converted from previous wiki install'));
    $newpage->allow_edit = $page['allow_edit'];
    $result = $newpage->save();

    if (PEAR::isError($result))
    {
        \core\Error::log($result);
        return FALSE;
    }

    $version = new Version('wiki_pages');
    $version->setSource($newpage);
    $version->setApproved(1);
    $version->save();

    return TRUE;
}

function convertInterwiki($interwiki)
{
    \core\Core::initModClass('wiki', 'WikiManager.php');
    \core\Core::initModClass('wiki', 'InterWiki.php');

    $newinterwiki = new InterWiki;
    $newinterwiki->setLabel($interwiki['label']);
    $newinterwiki->setUrl($interwiki['url']);
    $newinterwiki->save(FALSE);

    return TRUE;
}

function convertImage($image)
{
    \core\Core::initModClass('wiki', 'WikiImage.php');

    $newimage = new WikiImage;
    $newimage->setOwnerId(Current_User::getId());
    $newimage->setCreated($image['created']);
    $newimage->setFilename($image['filename']);
    $newimage->setSize($image['size']);
    $newimage->setType($image['type']);
    $newimage->setSummary($image['summary']);

    $db = new \core\DB('wiki_images');
    $result = $db->saveObject($newimage);
    if (PEAR::isError($result))
    {
        \core\Error::log($result);
        return FALSE;
    }

    if (OLD_WIKI_IMAGES . $image['filename'] != PHPWS_HOME_DIR . 'images/wiki/' . $newimage->getFilename())
    {
        if (!@copy(OLD_WIKI_IMAGES . $image['filename'],
            PHPWS_HOME_DIR . 'images/wiki/' . $newimage->getFilename()))
        {
            return FALSE;
        }
    }

    return TRUE;
}

?>