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

$settings = array('show_on_home' => 1,
                  'allow_anon_view' => 1,
                  'allow_page_edit' => 0,
                  'allow_image_upload' => 0,
                  'allow_bbcode' => 0,
                  'ext_chars_support' => 0,
                  'add_to_title' => 1,
                  'format_title' => 0,
                  'show_modified_info' => 1,
                  'diff_type' => 'two_col',
                  'monitor_edits' => 0,
                  'admin_email' => PHPWS_User::getUserSetting('site_contact'),
                  'email_text' => '[page] has been updated.  Go to [url] to view it.',
                  'default_page' => 'FrontPage',
                  'ext_page_target' => '_blank',
                  'immutable_page' => 1,
                  'raw_text' => 0,
                  'print_view' => 1,
                  'what_links_here' => 1,
                  'recent_changes' => 1,
                  'random_page' => 1,
                  'discussion' => 1,
                  'discussion_anon' => 0);

?>