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

class Elections_Ballot {

    public $id             = 0;
    public $key_id         = 0;
    public $title          = null;
    public $description    = null;
    public $image_id       = 0;
    public $pubview        = 1;
    public $pubvote        = 0;
    public $votegroups     = null;
    public $opening        = 0;
    public $closing        = 0;
    public $show_in_block  = 1;
    public $minchoice      = 1;
    public $maxchoice      = 1;
    public $ranking        = 0;
    public $custom1label   = null;
    public $custom2label   = null;
    public $custom3label   = null;
    public $custom4label   = null;

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
        $db = new PHPWS_DB('elections_ballots');
        $result = $db->loadObject($this);
        if (PEAR::isError($result)) {
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

    public function setImage_id($image_id)
    {
        $this->image_id = $image_id;
    }

    public function setPubview($pubview)
    {
        $this->pubview = $pubview;
    }

    public function setPubvote($pubvote)
    {
        $this->pubvote = $pubvote;
    }

    public function setVotegroups($votegroups)
    {
        $this->votegroups = implode(":", $votegroups);
    }

    public function setShow_in_block($show_in_block)
    {
        $this->show_in_block = $show_in_block;
    }

    public function setMinchoice($minchoice)
    {
        $this->minchoice = $minchoice;
    }

    public function setMaxchoice($maxchoice)
    {
        $this->maxchoice = $maxchoice;
    }

    public function setRanking($ranking)
    {
        $this->ranking = $ranking;
    }

    public function setCustom1label($custom1label)
    {
        $this->custom1label = PHPWS_Text::parseInput($custom1label);
    }

    public function setCustom2label($custom2label)
    {
        $this->custom2label = PHPWS_Text::parseInput($custom2label);
    }

    public function setCustom3label($custom3label)
    {
        $this->custom3label = PHPWS_Text::parseInput($custom3label);
    }

    public function setCustom4label($custom4label)
    {
        $this->custom4label = PHPWS_Text::parseInput($custom4label);
    }


    public function getTitle($print=false)
    {
        if (empty($this->title)) {
            return null;
        }

        if ($print) {
            return PHPWS_Text::parseOutput($this->title);
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

    public function getOpening($type=ELEC_DATE_FORMAT)
    {
        if ($this->opening) {
            return strftime($type, $this->opening);
        } else {
            return strftime($type, time());
        }
    }

    public function getClosing($type=ELEC_DATE_FORMAT)
    {
        if ($this->closing) {
            return strftime($type, $this->closing);
        } else {
            return null;
        }
    }

    public function getCustom1label($print=false)
    {
        if (empty($this->custom1label)) {
            return null;
        }

        if ($print) {
            return PHPWS_Text::parseOutput($this->custom1label);
        } else {
            return $this->custom1label;
        }
    }

    public function getCustom2label($print=false)
    {
        if (empty($this->custom2label)) {
            return null;
        }

        if ($print) {
            return PHPWS_Text::parseOutput($this->custom2label);
        } else {
            return $this->custom2label;
        }
    }

    public function getCustom3label($print=false)
    {
        if (empty($this->custom3label)) {
            return null;
        }

        if ($print) {
            return PHPWS_Text::parseOutput($this->custom3label);
        } else {
            return $this->custom3label;
        }
    }

    public function getCustom4label($print=false)
    {
        if (empty($this->custom4label)) {
            return null;
        }

        if ($print) {
            return PHPWS_Text::parseOutput($this->custom4label);
        } else {
            return $this->custom4label;
        }
    }



    public function view()
    {
        if (!$this->id) {
            PHPWS_Core::errorPage(404);
        }

        $key = new Key($this->key_id);

        if (!$key->allowView()) {
            Current_User::requireLogin();
        }

        $voteable = $this->can_vote();

        if ($voteable == '1') {
            if (javascriptEnabled()) {
                javascriptMod('elections', 'utilities');
                //                javascript('modules/elections/checkvotes');
            }
            $form = new PHPWS_Form('elections_vote');

            $form->addHidden('module', 'elections');
            $form->addHidden('uop', 'post_vote');
            $form->addHidden('ballot_id', $this->id);
            if (!javascriptEnabled()) {
                $form->addSubmit(dgettext('elections', 'Vote'));
            }

            $tpl = $form->getTemplate();
            if ($this->ranking) {
                $type = 'text';
            } else {
                $type = 'check';
            }
            if (javascriptEnabled()) {
                $submit_vars = array("MIN"=>$this->minchoice, 'MAX'=>$this->maxchoice, 'SUBMIT_LABEL'=>dgettext('elections', 'Vote'), 'TYPE'=>$type);
                $tpl['VOTE_BUTTON'] = javascriptMod('elections', 'checkvotes', $submit_vars);
            }
            $tpl['MSG'] = '<b>' . dgettext('elections', 'Voting is open for this election.') . '</b>';
            if ($this->maxchoice > 1) {
                $tpl['MSG'] .= '&nbsp;' . '<i>' . sprintf(dgettext('elections', 'You may cast from %s to %s votes on this ballot.'), $this->minchoice, $this->maxchoice) . '</i>';
            } else {
                $tpl['MSG'] .= '&nbsp;' . '<i>' . sprintf(dgettext('elections', 'You may cast %s vote on this ballot.'), $this->maxchoice) . '</i>';
            }
        } else {
            $tpl['MSG'] = '<b>' . $voteable . '</b>';
        }

        Layout::addPageTitle($this->getTitle());

        $tpl['BALLOT_LINKS'] = $this->ballotLinks();
        $tpl['TITLE'] = $this->getTitle(true);
        $tpl['DESCRIPTION'] = PHPWS_Text::parseTag($this->getDescription(true));
        $tpl['FILE'] = $this->getFile();

        if (!empty($this->opening)) {
            $tpl['OPENING_TEXT'] = dgettext('elections', 'Voting opens');
            $tpl['OPENING'] = $this->getOpening();
        }

        if (!empty($this->closing)) {
            $tpl['CLOSING_TEXT'] = dgettext('elections', 'Voting closes');
            $tpl['CLOSING'] = $this->getClosing();
        }

        if (!empty($this->minchoice)) {
            $tpl['MINCHOICE_TEXT'] = dgettext('elections', 'Minimum choices allowed');
            $tpl['MINCHOICE'] = $this->minchoice;
        }

        if (!empty($this->maxchoice)) {
            $tpl['MAXCHOICE_TEXT'] = dgettext('elections', 'Maximum choices allowed');
            $tpl['MAXCHOICE'] = $this->maxchoice;
        }

        if (Current_User::isUnrestricted('elections')) {

            $tpl['PUBVIEW_TEXT'] = dgettext('elections', 'Public may view');
            if (!empty($this->pubview)) {
                $tpl['PUBVIEW'] = dgettext('elections', 'Yes');
            } else {
                $tpl['PUBVIEW'] = dgettext('elections', 'No');
            }

            $tpl['PUBVOTE_TEXT'] = dgettext('elections', 'Public may vote');
            if (!empty($this->pubvote)) {
                $tpl['PUBVOTE'] = dgettext('elections', 'Yes');
            } else {
                $tpl['PUBVOTE'] = dgettext('elections', 'No');
            }

            if (!empty($this->votegroups)) {
                $tpl['VOTEGROUPS_TEXT'] = dgettext('elections', 'Voting Groups');
                $votegroups = explode(":", $this->votegroups);
                $gnames = null;
                foreach ($votegroups as $row) {
                    PHPWS_Core::initModClass('users', 'Group.php');
                    $group = new PHPWS_Group($row);
                    $gnames .= $group->getName() . ', ';
                }
                $tpl['VOTEGROUPS'] = substr($gnames, 0, -2);
            }

            $tpl['SHOWIN_BLOCK_TEXT'] = dgettext('elections', 'List in side-box');
            if (!empty($this->show_in_block)) {
                $tpl['SHOWIN_BLOCK'] = dgettext('elections', 'Yes');
            } else {
                $tpl['SHOWIN_BLOCK'] = dgettext('elections', 'No');
            }

        }


        $candidates = $this->getAllCandidates();

        if (PHPWS_Error::logIfError($candidates)) {
            $this->election->content = dgettext('elections', 'An error occurred when accessing this ballot\'s candidates.');
            return;
        }

        if ($candidates) {
            foreach ($candidates as $candidate) {
                if ($voteable == '1') {
                    $tpl['candidates'][] = $candidate->viewTpl(true);
                } else {
                    $tpl['candidates'][] = $candidate->viewTpl();
                }
            }
        } else {
            if (Current_User::allow('elections'))
            $tpl['EMPTY'] = dgettext('elections', 'Click on "New candidate" to start.');
        }

        $key->flag();

        return PHPWS_Template::process($tpl, 'elections', 'view_ballot.tpl');
    }


    public function getAllCandidates($limit=false)
    {
        PHPWS_Core::initModClass('elections', 'ELEC_Candidate.php');
        $db = new PHPWS_DB('elections_candidates');
        $db->addOrder('title desc');
        $db->addWhere('ballot_id', $this->id);
        if ($limit) {
            $db->setLimit((int)$limit);
        }
        $result = $db->getObjects('Elections_Candidate');
        return $result;
    }


    public function getQtyCandidates()
    {
        $db = new PHPWS_DB('elections_candidates');
        $db->addWhere('ballot_id', $this->id);
        $qty = $db->count();
        return $qty;
    }


    public function getVotes()
    {
        $db = new PHPWS_DB('elections_votes');
        $db->addWhere('ballot_id', $this->id);
        $qty = $db->count();
        return $qty;
    }


    public function ballotLinks()
    {
        $links = array();

        if (Current_User::allow('elections')) {
            $vars['aop']  = 'edit_candidate';
            $vars['ballot_id'] = $this->id;
            $links[] = PHPWS_Text::secureLink(dgettext('elections', 'Add Candidate'), 'elections', $vars);
        }

        if (Current_User::allow('elections')) {
            $vars['id'] = $this->id;
            $vars['aop']  = 'edit_ballot';
            $links[] = PHPWS_Text::secureLink(dgettext('elections', 'Edit ballot'), 'elections', $vars);
        }

        if (is_array(Election::navLinks())) {
            $links = array_merge($links, Election::navLinks());
        }

        if($links)
        return implode(' | ', $links);
    }


    public function delete()
    {
        if (!$this->id) {
            return;
        }

        /* delete the related candidates */
        $db = new PHPWS_DB('elections_candidates');
        $db->addWhere('ballot_id', $this->id);
        PHPWS_Error::logIfError($db->delete());

        /* delete the related votes */
        $db = new PHPWS_DB('elections_votes');
        $db->addWhere('ballot_id', $this->id);
        PHPWS_Error::logIfError($db->delete());

        /* delete the ballot */
        $db = new PHPWS_DB('elections_ballots');
        $db->addWhere('id', $this->id);
        PHPWS_Error::logIfError($db->delete());

        Key::drop($this->key_id);

    }


    public function rowTag()
    {
        $links = null;
        $vars['id'] = $this->id;

        if (Current_User::isUnrestricted('elections')) {
            $vars['aop']  = 'edit_candidate';
            $vars['ballot_id'] = $this->id;
            $links[] = PHPWS_Text::secureLink(dgettext('elections', 'Add Candidate'), 'elections', $vars);
            $vars['aop']  = 'edit_ballot';
            $links[] = PHPWS_Text::secureLink(dgettext('elections', 'Edit'), 'elections', $vars);
            $vars['aop'] = 'delete_ballot';
            $js['ADDRESS'] = PHPWS_Text::linkAddress('elections', $vars, true);
            $js['QUESTION'] = sprintf(dgettext('elections', 'Are you sure you want to delete the ballot %s?'), $this->getTitle());
            $js['LINK'] = dgettext('elections', 'Delete');
            $links[] = javascript('confirm', $js);
        }

        $tpl['TITLE'] = $this->viewLink();
        $tpl['DESCRIPTION'] = $this->getListDescription(120);
        if ($this->image_id) {
            $tpl['THUMBNAIL'] = $this->getThumbnail(true);
        } else {
            $tpl['THUMBNAIL'] = null;
        }
        //        $tpl['OPENS'] = $this->getOpening('%H:%M %a, %b %d, %y');
        //        $tpl['CLOSES'] = $this->getClosing('%H:%M %a, %b %d, %y');
        $tpl['OPENS'] = $this->getOpening('%H:%M %D');
        $tpl['CLOSES'] = $this->getClosing('%H:%M %D');
        $tpl['CANDIDATES'] = $this->getQtyCandidates();
        if (Current_User::isUnrestricted('elections')) {
            $tpl['VOTES'] = $this->getVotes();
        }
        if($links)
        $tpl['ACTION'] = implode(' | ', $links);
        return $tpl;
    }


    public function save()
    {
        $db = new PHPWS_DB('elections_ballots');

        $result = $db->saveObject($this);
        if (PEAR::isError($result)) {
            return $result;
        }

        $this->saveKey();

    }


    public function saveKey()
    {
        if (empty($this->key_id)) {
            $key = new Key;
        } else {
            $key = new Key($this->key_id);
            if (PEAR::isError($key->_error)) {
                $key = new Key;
            }
        }

        $key->setModule('elections');
        $key->setItemName('ballot');
        $key->setItemId($this->id);

        if (MOD_REWRITE_ENABLED) {
            $key->setUrl('elections/' . $this->id);
        } else {
            $key->setUrl('index.php?module=elections&amp;id=' . $this->id);
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
            $db = new PHPWS_DB('elections_ballots');
            $db->addWhere('id', $this->id);
            $db->addValue('key_id', $this->key_id);
            PHPWS_Error::logIfError($db->update());
        }
        return true;
    }


    public function viewLink($bare=false, $tpl=false)
    {
        PHPWS_Core::initCoreClass('Link.php');
        $link = new PHPWS_Link($this->title, 'elections', array('ballot'=>$this->id));
        $link->rewrite = MOD_REWRITE_ENABLED;

        if ($bare) {
            return $link->getAddress();
        } elseif ($tpl) {
            $tpl = array();
            $tpl['BALLOT_LINK'] = $link->get();
            return $tpl;
        } else {
            return $link->get();
        }

    }


    function can_vote()
    {
        $now = time();
        $msg = NULL;

        /* check the date */
        if ($now >= $this->opening) {
            if ($now <= $this->closing) {
                /* check if they've already voted */
                if (!$this->already_voted()) {
                    /* check to see if public is allowed */
                    if ($this->pubvote) {
                        $msg = '1';
                        /* if it's not are they logged in */
                    } elseif (isset($_SESSION['User']->username)) {
                        /* is there a group restriction */
                        if (isset($this->votegroups)){
                            $votegroups = explode(":", $this->votegroups);
                            $votegroups_array = array_intersect($votegroups, $_SESSION['User']->_groups);
                            // test                            print_r($votegroups_array); exit;
                            /* if they're in the group */
                            if(count($votegroups_array) > 0 or Current_User::isUnrestricted('elections')) {
                                $msg = '1';
                            } else {
                                $msg = dgettext('elections', 'You are not a member of an authorized voting group for this ballot.');
                            }
                            /* no group restriction */
                        } else {
                            $msg = '1';
                        }
                        /* no public and not logged in */
                    } else {
                        $msg = dgettext('elections', 'Public voting is not allowed for this ballot. You must log in first.');
                    }
                    /* they've already voted */
                } else {
                    $msg = dgettext('elections', 'You have already voted in this ballot.');
                }
                /* voting is closed */
            } else {
                $msg = dgettext('elections', 'Voting has closed for this ballot.');
            }
            /* voting isn't open yet */
        } else {
            $msg = dgettext('elections', 'Voting has not opened yet for this ballot.');
        }

        return $msg;
    }// END FUNC can_vote


    function already_voted()
    {
        if ($this->pubvote) {
            /* check for a cookie */
            if (isset($_COOKIE["BALLOT_$this->id"]) && ($_COOKIE["BALLOT_$this->id"] == $this->id))
            return true;
            else
            return false;
        } else {
            /* check the voters log */
            $db = new PHPWS_DB('elections_votes');
            $db->addWhere('username', Current_User::getUsername());
            $db->addWhere('ballot_id', $this->id);
            $db->addColumn('id');
            $result = $db->select('col');
            if (empty($result)) {
                return false;
            } else {
                return true;
            }
        }
    }// END FUNC already_voted





}

?>