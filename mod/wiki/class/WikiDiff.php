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
 * $Id: WikiDiff.php,v 1.9 2007/05/28 19:00:16 blindman1344 Exp $
 */

require_once 'Text/Diff.php';
require_once 'Text/Diff/Renderer.php';

class WikiDiff extends Text_Diff_Renderer
{
    var $_output = NULL;
    var $_format = 'two_col';


    /**
     * Constructor
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function WikiDiff($format='two_col', $lines=3)
    {
        $this->_format = $format;
        $this->_leading_context_lines = $lines;
        $this->_trailing_context_lines = $lines;
    }

    /**
     * Diff
     *
     * Main function: Calls the diff and render, then outputs to layout
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function diff($oVer, $nVer)
    {
        Core\Core::initModClass('wiki', 'OldWikiPage.php');
        $olderpage = new OldWikiPage();
        $newerpage = new OldWikiPage();

        $db = new PHPWS_DB('wiki_pages_version');
        $db->addWhere('title', $_REQUEST['page']);
        $db->addWhere('vr_number', $oVer);
        $db->loadObject($olderpage);

        $db->reset();
        $db->addWhere('title', $_REQUEST['page']);
        $db->addWhere('vr_number', $nVer);
        $db->loadObject($newerpage);

        // Need to parse the text, but we can't call parseOutput or this module's
        // transform function.  They both do too much parsing and can't be used
        // for a diff.  So, we call what is needed directly:
        $oPagetext = htmlspecialchars(str_replace("&#39;", "'", $olderpage->getPagetext(FALSE)));
        $nPagetext = htmlspecialchars(str_replace("&#39;", "'", $newerpage->getPagetext(FALSE)));
        if (!(ALLOW_PROFANITY))
        {
            $oPagetext = PHPWS_Text::profanityFilter($oPagetext);
            $nPagetext = PHPWS_Text::profanityFilter($nPagetext);
        }
        // End diff text parsing

        $oPagetext = explode("\n", $oPagetext);
        $nPageText = explode("\n", $nPagetext);

        $diff = new Text_Diff($oPagetext, $nPageText);
        $this->render($diff);

        $tags = array();
        $tags['TITLE'] = dgettext('wiki', 'Difference between revisions');
        $tags['BACK_PAGE'] = PHPWS_Text::moduleLink(dgettext('wiki', 'Back to Page'), 'wiki', array('page'=>$_REQUEST['page']));
        $tags['BACK_HISTORY'] = PHPWS_Text::moduleLink(dgettext('wiki', 'Back to History'), 'wiki', array('page_op'=>'history',
                                'page_id'=>$newerpage->getSourceId()));
        $tags['DIFF'] = $this->_output;
        $tags['OLDER_VERSION'] = $olderpage->getVrNumber();
        $tags['NEWER_VERSION'] = $newerpage->getVrNumber();
        $tags['OLDER_UPDATED'] = $olderpage->getUpdated();
        $tags['NEWER_UPDATED'] = $newerpage->getUpdated();
        $tags['VERSION_LABEL'] = dgettext('wiki', 'Version');

        return PHPWS_Template::process($tags, 'wiki', 'diff/' . $this->_format . '/diff.tpl');
    }

    /**
     * Block Header
     *
     * This is used to inform the user what lines they are looking at
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function _blockHeader($xbeg, $xlen, $ybeg, $ylen)
    {
        $tags = array();

        if ($xlen > 1)
        {
            $tags['OLD_LINES'] = 'Lines ' . $xbeg . ' - ' . ($xbeg + $xlen - 1);
        }
        else
        {
            $tags['OLD_LINES'] = 'Line ' . $xbeg;
        }

        if ($ylen > 1)
        {
            $tags['NEW_LINES'] = 'Lines ' . $ybeg . ' - ' . ($ybeg + $ylen - 1);
        }
        else
        {
            $tags['NEW_LINES'] = 'Line ' . $ybeg;
        }

        $this->_output .= PHPWS_Template::process($tags, 'wiki', 'diff/' . $this->_format . '/blockheader.tpl');
    }

    /**
     * Lines
     *
     * Prepares the lines for output
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function _lines($lines, $template)
    {
        foreach ($lines as $line)
        {
            if($line == NULL)
            {
                $this->_output .= PHPWS_Template::process(array('LINE'=>'&nbsp;'), 'wiki',
                                  'diff/' . $this->_format . '/' . $template . '.tpl');
            }
            else
            {
                $this->_output .= PHPWS_Template::process(array('LINE'=>$line), 'wiki',
                                  'diff/' . $this->_format . '/' . $template . '.tpl');
            }
        }
    }

    /**
     * Context
     *
     * Handles the output of the context lines
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function _context($lines)
    {
        // Nothing else to do for context lines, so pass lines on to _lines
        $this->_lines($lines, 'context');
    }

    /**
     * Added
     *
     * Handles the output of the added lines
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function _added($lines)
    {
        // Nothing else to do for added lines, so pass lines on to _lines
        $this->_lines($lines, 'added');
    }

    /**
     * Deleted
     *
     * Handles the output of the deleted lines
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function _deleted($lines)
    {
        // Nothing else to do for deleted lines, so pass lines on to _lines
        $this->_lines($lines, 'deleted');
    }

    /**
     * Changed
     *
     * Handles the output of the changed lines
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function _changed($orig, $final)
    {
        // Odds of the contents of a line being the time is very small
        $pad = time();
        $orig = array_pad($orig, count($final), $pad);
        $final = array_pad($final, count($orig), $pad);

        while (($origLine = each($orig)) && ($finalLine = each($final)))
        {
            if ($origLine[1] == $pad)
            {
                $this->_lines(array(''), 'changed_orig_notexist');
            }
            else
            {
                $this->_lines(array($origLine[1]), 'changed_orig_content');
            }

            if ($finalLine[1] == $pad)
            {
                $this->_lines(array(''), 'changed_final_notexist');
            }
            else
            {
                $this->_lines(array($finalLine[1]), 'changed_final_content');
            }
        }
    }
}

?>