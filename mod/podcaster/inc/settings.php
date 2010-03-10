<?php
/**
 * podcaster - phpwebsite module
 *
 * See docs/AUTHORS and docs/COPYRIGHT for relevant info.
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
 * @version $Id$
 * @author Verdon Vaillancourt <verdonv at gmail dot com>
 */

$settings['channel_limit']          = 10;
$settings['req_approval']           = 1;
$settings['editor']                 = null;
$settings['webmaster']              = null;
$settings['copyright']              = sprintf('Copyright %s', date('Y'));
$settings['max_width']              = 160;
$settings['max_height']             = 160;
$settings['mod_folders_only']       = 1;
$settings['rm_media']               = 1;
$settings['show_block']             = 1;
$settings['block_on_home_only']     = 1;
$settings['block_order_by_rand']    = 1;
$settings['cache_timeout']          = 3600;

?>