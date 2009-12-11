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

/*****************************************************************************
 * DATE FORMAT SETUP                                                         *
 *                                                                           *
 * This controls how dates will be displayed within the wiki module.         *
 *****************************************************************************/

define('WIKI_DATE_FORMAT', '%m/%d/%Y %r');

/*****************************************************************************
 * WIKI PARSER SELECTION                                                     *
 *                                                                           *
 * Select which parser you want this module to use from the list below. Only *
 * one parser can be selected.  Keep in mind that changing this setting will *
 * cause the default pages (FrontPage, SamplePage, WikiSandBox) and any      *
 * pages already created to render incorrectly.  You will have to update     *
 * these pages to the correct wiki syntax.  Links have been provided below   *
 * to pages showing correct syntax for that parser.                          *
 *                                                                           *
 * Note: Only the Text_Wiki parser is included with phpWebsite.  If you want *
 * to use any of the other parsers, you will have to download them from the  *
 * links provided.                                                           *
 *****************************************************************************/

/*
 * Text_Wiki  (Highly Recommended)
 * http://pear.php.net/package/Text_Wiki/
 *
 * Version required: Included with phpWebSite
 * Syntax: SamplePage (provided by this module)
 */
define('WIKI_PARSER', 'Default');

/*
 * Text_Wiki_Creole
 * http://pear.php.net/package/Text_Wiki_Creole/
 *
 * Version required: 0.4.2 or later
 * Syntax: http://www.wikicreole.org
 */
//define('WIKI_PARSER', 'Creole');

/*
 * Text_Wiki_Doku
 * http://pear.php.net/package/Text_Wiki_Doku/
 *
 * Version required: Greater than 0.0.1
 * Syntax: http://wiki.splitbrain.org/wiki:syntax
 */
//define('WIKI_PARSER', 'Doku');

/*
 * Text_Wiki_Mediawiki
 * http://pear.php.net/package/Text_Wiki_Mediawiki/
 *
 * Version required: 0.1.0 or later
 * Syntax: http://meta.wikimedia.org/wiki/Help:Editing#The_wiki_markup
 */
//define('WIKI_PARSER', 'Mediawiki');

/*
 * Text_Wiki_Tiki
 * http://pear.php.net/package/Text_Wiki_Tiki/
 *
 * Version required: Greater than 0.0.1
 * Syntax: http://tikiwiki.org/tiki-index.php?page=WikiSyntax
 */
//define('WIKI_PARSER', 'Tiki');

?>