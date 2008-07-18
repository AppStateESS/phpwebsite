<?php
/**
    * sitemap - phpwebsite module
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
    * @version $Id: settings.php,v 1.4 2008/07/16 19:07:14 verdonv Exp $
    * @author Verdon Vaillancourt <verdonv at users dot sourceforge dot net>
*/

$settings['respect_privs']          = 1;
$settings['local_only']             = 1;
$settings['use_change']             = 1;
$settings['change_freq']            = 5;
$settings['use_lastmod']            = 1;
$settings['use_priority']           = 1;
$settings['cache_timeout']          = 3600;
$settings['allow_feed']             = 1;
$settings['addkeys']                = 1;
$settings['exclude_keys']           = serialize(array('filecabinet'));

?>