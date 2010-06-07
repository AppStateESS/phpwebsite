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
 * @version $Id: install.php,v 1.18 2006/11/05 17:07:07 blindman1344 Exp $
 */

function wiki_install(&$content)
{
    Core\Core::initModClass('wiki', 'WikiManager.php');
    Core\Core::initModClass('wiki', 'WikiPage.php');
    Core\Core::initModClass('version', 'Version.php');

    // Adding pages that ship with the module

    if (file_exists(PHPWS_SOURCE_DIR . 'mod/wiki/boost/frontpage.txt'))
    {
        $frontpage = new WikiPage('FrontPage');
        $frontpage->setPagetext(implode('', file(PHPWS_SOURCE_DIR . 'mod/wiki/boost/frontpage.txt')));
        $frontpage->setOwnerId(Current_User::getId());
        $frontpage->setEditorId(Current_User::getId());
        $frontpage->setCreated(mktime());
        $frontpage->setUpdated(mktime());
        $frontpage->setComment('Provided by Wiki install');
        $frontpage->save();

        $version1 = new Version('wiki_pages');
        $version1->setSource($frontpage);
        $version1->setApproved(1);
        $version1->save();
    }

    if (file_exists(PHPWS_SOURCE_DIR . 'mod/wiki/boost/samplepage.txt'))
    {
        $samplepage = new WikiPage('SamplePage');
        $samplepage->setPagetext(implode('', file(PHPWS_SOURCE_DIR . 'mod/wiki/boost/samplepage.txt')));
        $samplepage->setOwnerId(Current_User::getId());
        $samplepage->setEditorId(Current_User::getId());
        $samplepage->setCreated(mktime());
        $samplepage->setUpdated(mktime());
        $samplepage->setComment('Provided by Wiki install');
        $samplepage->allow_edit = 0;
        $samplepage->save();

        $version2 = new Version('wiki_pages');
        $version2->setSource($samplepage);
        $version2->setApproved(1);
        $version2->save();
    }

    if (file_exists(PHPWS_SOURCE_DIR . 'mod/wiki/boost/sandbox.txt'))
    {
        $sandbox = new WikiPage('WikiSandBox');
        $sandbox->setPagetext(implode('', file(PHPWS_SOURCE_DIR . 'mod/wiki/boost/sandbox.txt')));
        $sandbox->setOwnerId(Current_User::getId());
        $sandbox->setEditorId(Current_User::getId());
        $sandbox->setCreated(mktime());
        $sandbox->setUpdated(mktime());
        $sandbox->setComment('Provided by Wiki install');
        $sandbox->save();

        $version3 = new Version('wiki_pages');
        $version3->setSource($sandbox);
        $version3->setApproved(1);
        $version3->save();
    }

    // Adding first interwiki link
    Core\Core::initModClass('wiki', 'InterWiki.php');
    $interwiki = new InterWiki;
    $interwiki->setLabel('Wikipedia');
    $interwiki->setUrl('http://en.wikipedia.org/wiki/%s');
    $interwiki->save(FALSE);

    return TRUE;
}

?>