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

class Text_Wiki_Parse_Template extends Text_Wiki_Parse
{
    /**
     * The regular expression used to parse the source text.
     *
     * @access public
     *
     * @var string
     *
     * @see parse()
     */
    var $regex = "/{{Template:({*?.*}*?)}}/U";


    /**
     * Generates a replacement for the matched text.
     *
     * @access public
     *
     * @param array &$matches The array of matches from parse().
     *
     * @return string Contents of the template to insert into source text.
     */
    function process(&$matches)
    {
        // Get the template from the database.
        $wikipage = new WikiPage($matches[1]);

        // Do not call transform() here. This function is called during a
        // transform, so the returned value will be transformed by that call.
        $parsed_text = str_replace("&#39;", "'", $wikipage->pagetext);

        // Check to see if there are templates in the template
        $parsed_text = preg_replace_callback(
            $this->regex,
            array(&$this, 'process'),
            $parsed_text
        );

        return $parsed_text;
    }
}
?>