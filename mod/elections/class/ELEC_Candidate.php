<?php
/**
 * elections - phpwebsite module
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

class Elections_Candidate {

    public $id             = 0;
    //    public $key_id         = 0;
    public $ballot_id      = 0;
    public $title          = null;
    public $description    = null;
    public $image_id       = 0;
    public $votes          = 0;
    public $custom1        = null;
    public $custom2        = null;
    public $custom3        = null;
    public $custom4        = null;

    public $_error         = null;


    public function __construct($id=0)
    {
        if (!$id) {
            return;
        }

        $this->id = (int)$id;
        $this->init();
    }


    public function init()
    {
        $db = new PHPWS_DB('elections_candidates');
        $result = $db->loadObject($this);
        if (PHPWS_Error::isError($result)) {
            $this->_error = & $result;
            $this->id = 0;
        } elseif (!$result) {
            $this->id = 0;
        }
    }


    public function setTitle($title)
    {
        $this->title = strip_tags($title);
    }

    public function setDescription($description)
    {
        $this->description = PHPWS_Text::parseInput($description);
    }

    public function setBallot_id($ballot_id)
    {
        if (!is_numeric($ballot_id)) {
            return false;
        } else {
            $this->ballot_id = (int)$ballot_id;
            return true;
        }
    }

    public function setImage_id($image_id)
    {
        $this->image_id = $image_id;
    }

    public function setVotes($votes)
    {
        if (!is_numeric($votes)) {
            return false;
        } else {
            $this->votes = (int)$votes;
            return true;
        }
    }

    public function setCustom1($custom1)
    {
        $this->custom1 = PHPWS_Text::parseInput($custom1);
    }

    public function setCustom2($custom2)
    {
        $this->custom2 = PHPWS_Text::parseInput($custom2);
    }

    public function setCustom3($custom3)
    {
        $this->custom3 = PHPWS_Text::parseInput($custom3);
    }

    public function setCustom4($custom4)
    {
        $this->custom4 = PHPWS_Text::parseInput($custom4);
    }


    public function getTitle($print=false, $breadcrumb=false)
    {
        if (empty($this->title)) {
            return null;
        }

        if ($print) {
            if ($breadcrumb) {
                return $this->getBallot(true) . ' &#187; ' . PHPWS_Text::parseOutput($this->title);
            } else {
                return PHPWS_Text::parseOutput($this->title);
            }
        } else {
            return $this->title;
        }
    }

    public function getDescription($print=false)
    {
        if (empty($this->description)) {
            return null;
        }

        if ($print) {
            return PHPWS_Text::parseOutput($this->description);
        } else {
            return $this->description;
        }
    }

    public function getListDescription($length=60){
        return substr(ltrim(strip_tags(str_replace('<br />', ' ', $this->getDescription(true)))), 0, $length) . ' ...';
    }

    public function getFile()
    {
        if (!$this->image_id) {
            return null;
        }
        return Cabinet::getTag($this->image_id);
    }

    public function getThumbnail($link=false)
    {
        if (empty($this->image_id)) {
            return null;
        }

        PHPWS_Core::initModClass('filecabinet', 'Cabinet.php');
        $file = Cabinet::getFile($this->image_id);

        if ($file->isImage(true)) {
            $file->allowImageLink(false);
            if ($link) {
                return sprintf('<a href="%s">%s</a>', $this->viewLink(true), $file->getThumbnail());
            } else {
                return $file->getThumbnail();
            }
        } elseif ($file->isMedia() && $file->_source->isVideo()) {
            if ($link) {
                return sprintf('<a href="%s">%s</a>', $this->viewLink(), $file->getThumbnail());
            } else {
                return $file->getThumbnail();
            }
        } else {
            return $file->getTag();
        }
    }

    public function getVotes()
    {
        return $this->votes;
    }


    public function getBallot($print=false)
    {
        if (empty($this->ballot_id)) {
            return null;
        }

        if ($print) {
            PHPWS_Core::initModClass('elections', 'ELEC_Ballot.php');
            $ballot = new Elections_Ballot($this->ballot_id);
            return $ballot->viewLink();
        } else {
            return $this->ballot_id;
        }
    }






    public function getCustom1($print=false)
    {
        if (empty($this->custom1)) {
            return null;
        }

        if ($print) {
            return PHPWS_Text::parseOutput($this->custom1);
        } else {
            return $this->custom1;
        }
    }

    public function getCustom2($print=false)
    {
        if (empty($this->custom2)) {
            return null;
        }

        if ($print) {
            return PHPWS_Text::parseOutput($this->custom2);
        } else {
            return $this->custom2;
        }
    }

    public function getCustom3($print=false)
    {
        if (empty($this->custom3)) {
            return null;
        }

        if ($print) {
            return PHPWS_Text::parseOutput($this->custom3);
        } else {
            return $this->custom3;
        }
    }

    public function getCustom4($print=false)
    {
        if (empty($this->custom4)) {
            return null;
        }

        if ($print) {
            return PHPWS_Text::parseOutput($this->custom4);
        } else {
            return $this->custom4;
        }
    }



    public function view()
    {
        if (!$this->id) {
            PHPWS_Core::errorPage(404);
        }

        //        $key = new Key($this->key_id);

        //        if (!$key->allowView()) {
        //            Current_User::requireLogin();
        //        }

        $tpl = $this->getCustoms();
        $tpl['CANDIDATE_LINKS'] = $this->candidateLinks();

        Layout::addPageTitle($this->getTitle());
        $tpl['TITLE'] = $this->getTitle(true);
        $tpl['DESCRIPTION'] = PHPWS_Text::parseTag($this->getDescription(true));
        $tpl['FILE'] = $this->getFile();



        //        $key->flag();

        return PHPWS_Template::process($tpl, 'elections', 'view_candidate.tpl');
    }


    public function candidateLinks($addNav=true)
    {
        $links = array();

        if (Current_User::allow('elections')) {
            $vars['ballot_id'] = $this->ballot_id;
            $vars['candidate_id'] = $this->id;
            $vars['aop']  = 'edit_candidate';
            $links[] = PHPWS_Text::secureLink(dgettext('elections', 'Edit candidate'), 'elections', $vars);
        }

        if ($addNav) {
            if (is_array(Election::navLinks())) {
                $links = array_merge($links, Election::navLinks());
            }
        }

        if($links)
        return implode(' | ', $links);
    }


    public function delete()
    {
        if (!$this->id) {
            return;
        }

        /* delete the candidate */
        $db = new PHPWS_DB('elections_candidates');
        $db->addWhere('id', $this->id);
        PHPWS_Error::logIfError($db->delete());

        //        Key::drop($this->key_id);

    }


    public function rowTag()
    {
        $vars['candidate_id'] = $this->id;
        $vars['ballot_id'] = $this->ballot_id;

        if (Current_User::isUnrestricted('elections')) {
            $vars['aop']  = 'edit_candidate';
            $links[] = PHPWS_Text::secureLink(dgettext('elections', 'Edit'), 'elections', $vars);
            $vars['aop'] = 'delete_candidate';
            $js['ADDRESS'] = PHPWS_Text::linkAddress('elections', $vars, true);
            $js['QUESTION'] = sprintf(dgettext('elections', 'Are you sure you want to delete the candidate %s?'), $this->getTitle());
            $js['LINK'] = dgettext('elections', 'Delete');
            $links[] = javascript('confirm', $js);
        }

        $tpl['TITLE'] = $this->viewLink();
        $tpl['DESCRIPTION'] = $this->getListDescription(120);
        $tpl['BALLOT'] = $this->getBallot(true);
        $tpl['VOTES'] = $this->getVotes();
        if($links)
        $tpl['ACTION'] = implode(' | ', $links);
        return $tpl;
    }


    public function viewTpl($votebox=false)
    {
        $tpl = $this->getCustoms();
        $tpl['CANDIDATE_TITLE'] = $this->viewLink();
        $tpl['DESCRIPTION'] = $this->getDescription(true);
        $tpl['LINKS'] = $this->candidateLinks(false);
        if ($this->image_id) {
            $tpl['CANDIDATE_THUMBNAIL'] = $this->getThumbnail(true);
        } else {
            $tpl['CANDIDATE_THUMBNAIL'] = null;
        }
        if ($votebox) {
            $tpl['VOTE_BOX'] = $this->getVoteBox();
        }

        return $tpl;
    }


    public function save()
    {
        $db = new PHPWS_DB('elections_candidates');

        $result = $db->saveObject($this);
        if (PHPWS_Error::isError($result)) {
            return $result;
        }

        //        $this->saveKey();

    }


    public function saveKey()
    {
        if (empty($this->key_id)) {
            $key = new Key;
        } else {
            $key = new Key($this->key_id);
            if (PHPWS_Error::isError($key->_error)) {
                $key = new Key;
            }
        }

        $key->setModule('elections');
        $key->setItemName('candidate');
        $key->setItemId($this->id);

        if (MOD_REWRITE_ENABLED) {
            $key->setUrl('elections/' . $this->ballot_id . '/' . $this->id);
        } else {
            $key->setUrl('index.php?module=elections&amp;uop=view_candidate&amp;id=' . $this->id);
        }

        $key->active = 1;
        $key->setTitle($this->title);
        $key->setSummary($this->description);
        $result = $key->save();
        if (PHPWS_Error::logIfError($result)) {
            return false;
        }

        if (!$this->key_id) {
            $this->key_id = $key->id;
            $db = new PHPWS_DB('elections_candidates');
            $db->addWhere('id', $this->id);
            $db->addValue('key_id', $this->key_id);
            PHPWS_Error::logIfError($db->update());
        }
        return true;
    }


    public function viewLink($bare=false)
    {
        PHPWS_Core::initCoreClass('Link.php');
        $link = new PHPWS_Link($this->title, 'elections', array('ballot'=>$this->ballot_id, 'candidate'=>$this->id));
        $link->rewrite = MOD_REWRITE_ENABLED;

        if ($bare) {
            return $link->getAddress();
        } else {
            return $link->get();
        }
    }


    public function getVoteBox()
    {
        PHPWS_Core::initModClass('elections', 'ELEC_Ballot.php');
        $ballot = new Elections_Ballot($this->ballot_id);
        if (isset($_REQUEST['Candidate_Vote'][$this->id])) {
            $match = $_REQUEST['Candidate_Vote'][$this->id];
        } else {
            $match = null;
        }

        if ($ballot->ranking) {
            $tpl = null;
            if (javascriptEnabled()) {
                $tpl .= '<input type="button" name="-" onclick=\'javascript: subtractQty("Candidate_Vote['.$this->id.']");\' value="-" />';
            }
            $tpl .= '<input type="text" name="Candidate_Vote['.$this->id.']" id="Candidate_Vote['.$this->id.']" size="3" value="'.$match.'" />';
            if (javascriptEnabled()) {
                $tpl .= '<input type="button" name="+" onclick=\'javascript: document.getElementById("Candidate_Vote['.$this->id.']").value++;\' value="+" />';
            }
        } else {
            $tpl = PHPWS_Form::formCheckBox("Candidate_Vote[".$this->id."]", 1, $match, $this->id);
        }

        return $tpl;
    }


    public function getCustoms()
    {
        PHPWS_Core::initModClass('elections', 'ELEC_Ballot.php');
        $ballot = new Elections_Ballot($this->ballot_id);
        $tpl = null;
        if ($ballot->custom1label) {
            $tpl['CUSTOM1_LABEL'] = $ballot->getCustom1label(true);
            $tpl['CUSTOM1'] = $this->getCustom1(true);
        }

        if ($ballot->custom2label) {
            $tpl['CUSTOM2_LABEL'] = $ballot->getCustom2label(true);
            $tpl['CUSTOM2'] = $this->getCustom2(true);
        }

        if ($ballot->custom3label) {
            $tpl['CUSTOM3_LABEL'] = $ballot->getCustom3label(true);
            $tpl['CUSTOM3'] = $this->getCustom3(true);
        }

        if ($ballot->custom4label) {
            $tpl['CUSTOM4_LABEL'] = $ballot->getCustom4label(true);
            $tpl['CUSTOM4'] = $this->getCustom4(true);
        }

        return $tpl;
    }


    public function printCSVHeader()
    {
        $content = null;

        $content .= '"' . dgettext('elections', 'ID') . '",';
        $content .= '"' . dgettext('elections', 'Ballot ID') . '",';
        $content .= '"' . dgettext('elections', 'Title') . '",';
        $content .= '"' . dgettext('elections', 'Votes') . '",';

        /*  think about this in multi-ballot exports, could be messy
         PHPWS_Core::initModClass('elections', 'ELEC_Ballot.php');
         $ballot = new Elections_Ballot($this->ballot_id);
         if ($ballot->custom1label) {
         $content .= '"' . $ballot->getCustom1label(true) . '",';
         }
         if ($ballot->custom2label) {
         $content .= '"' . $ballot->getCustom2label(true) . '",';
         }
         if ($ballot->custom3label) {
         $content .= '"' . $ballot->getCustom3label(true) . '",';
         }
         if ($ballot->custom4label) {
         $content .= '"' . $ballot->getCustom4label(true) . '",';
         }
         */

        $content .= '"' . dgettext('elections', 'Description') . '"';

        $content .= "\n";
        return $content;
    }


    public function printCSV()
    {
        $content = null;

        $content .= '"' . $this->id . '",';
        $content .= '"' . $this->ballot_id . '",';
        $content .= '"' . $this->getTitle(true) . '",';
        $content .= '"' . $this->votes . '",';

        /*  think about this in multi-ballot exports, could be messy
         PHPWS_Core::initModClass('elections', 'ELEC_Ballot.php');
         $ballot = new Elections_Ballot($this->ballot_id);
         if ($ballot->custom1label) {
         $content .= '"' . $this->getCustom1(true) . '",';
         }
         if ($ballot->custom2label) {
         $content .= '"' . $this->getCustom2(true) . '",';
         }
         if ($ballot->custom3label) {
         $content .= '"' . $this->getCustom3(true) . '",';
         }
         if ($ballot->custom4label) {
         $content .= '"' . $this->getCustom4(true) . '",';
         }
         */

        $content .= '"' . $this->stripLF($this->getDescription(true)) . '"';

        $content .= "\n";
        return $content;
    }


    public function stripLF($str)
    {
        $str = str_replace("\r\n", '; ', $str);
        $str = str_replace("\n", '; ', $str);
        return $str;
    }





}

?>