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
    * @version $Id: $
    * @author Verdon Vaillancourt <verdonv at gmail dot com>
*/

class Elections_Vote {

    public $id             = 0;
    public $ballot_id      = 0;
    public $username       = null;
    public $votedate       = 0;
    public $ip             = null;

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
        $db = new PHPWS_DB('elections_votes');
        $result = $db->loadObject($this);
        if (PEAR::isError($result)) {
            $this->_error = & $result;
            $this->id = 0;
        } elseif (!$result) {
            $this->id = 0;
        }
    }


    public function getVotedate($type=ELEC_DATE_FORMAT)
    {
        if ($this->votedate) {
            return strftime($type, $this->votedate);
        } else {
            return null;
        }
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


    public function view()
    {
        if (!$this->id) {
            PHPWS_Core::errorPage(404);
        }


        $tpl['BALLOT_LINKS'] = $this->ballotLinks();
        $tpl['TITLE'] = $this->getTitle(true);
        $tpl['DESCRIPTION'] = PHPWS_Text::parseTag($this->getDescription(true));


        return PHPWS_Template::process($tpl, 'elections', 'view_vote.tpl');
    }



    public function voteLinks()
    {
        $links = array();

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
        
        $db = new PHPWS_DB('elections_votes');
        $db->addWhere('id', $this->id);
        PHPWS_Error::logIfError($db->delete());
    }


    public function rowTag()
    {
        $links = null;
        $vars['id'] = $this->id;

        if (Current_User::isUnrestricted('elections')) {
            $vars['aop'] = 'delete_vote';
            $js['ADDRESS'] = PHPWS_Text::linkAddress('elections', $vars, true);
            $js['QUESTION'] = sprintf(dgettext('elections', 'Are you sure you want to delete the vote %s? This will not take back the votes, just remove this record from the log.'), $this->id);
            $js['LINK'] = dgettext('elections', 'Delete');
            $links[] = javascript('confirm', $js);
        }

        $tpl['BALLOT'] = $this->getBallot(true);
        $tpl['USER'] = $this->username;
        $tpl['DATE'] = $this->getVotedate('%H:%M %D');
        $tpl['IP'] = $this->ip;
        if($links)
            $tpl['ACTION'] = implode(' | ', $links);

        return $tpl;
    }


    public function save()
    {
        $db = new PHPWS_DB('elections_votes');

        $result = $db->saveObject($this);
        if (PEAR::isError($result)) {
            return $result;
        }
    }


    public function viewLink($bare=false)
    {
        PHPWS_Core::initCoreClass('Link.php');
        $link = new PHPWS_Link($this->id, 'elections', array('ballot'=>$this->id));
        $link->rewrite = MOD_REWRITE_ENABLED;

        if ($bare) {
            return $link->getAddress();
        } else {
            return $link->get();
        }

    }


    public function printCSVHeader()
    {
        $content = null;

        $content .= '"' . dgettext('elections', 'ID') . '",';
        $content .= '"' . dgettext('elections', 'Ballot ID') . '",';
        $content .= '"' . dgettext('elections', 'Username') . '",';
        $content .= '"' . dgettext('elections', 'Vote Date') . '",';
        $content .= '"' . dgettext('elections', 'IP Address') . '"';

        $content .= "\n";
        return $content;
    }


    public function printCSV()
    {
        $content = null;

        $content .= '"' . $this->id . '",';
        $content .= '"' . $this->ballot_id . '",';
        $content .= '"' . $this->username . '",';
        $content .= '"' . $this->getVotedate('%H:%M %D') . '",';
        $content .= '"' . $this->ip . '"';

        $content .= "\n";
        return $content;
    }





}

?>