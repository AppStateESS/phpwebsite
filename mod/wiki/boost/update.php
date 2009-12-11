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
 * @package Wiki
 * @author Greg Meiste <greg.meiste+github@gmail.com>
 */

function wiki_update(&$content, $currentVersion)
{
    switch ($currentVersion)
    {
        case version_compare($currentVersion, '1.1.0', '<'):
            $content[] = '- Updated to new translation functions.';
            $content[] = '- Requesting a restricted page forwards user to the login screen.';

        case version_compare($currentVersion, '1.1.1', '<'):
            $content[] = '- Support new mod_rewrite method introduced in phpWebSite 1.5.0.';

        case version_compare($currentVersion, '1.1.2', '<'):
            $content[] = '- Fixed issues with anchors and the TOC. (Thanks Matt!)';

        case version_compare($currentVersion, '1.1.3', '<'):
            $content[] = '- Fix Skandinavian letters support.';
            $content[] = '- Removed references to broken help module.';
            $content[] = '- Use PHPWS_LIST_TOGGLE_CLASS and cacheQueries() for DBPager.';
    }

    return TRUE;
}

?>