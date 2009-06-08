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

PHPWS_Core::requireInc('elections', 'errordefines.php');
PHPWS_Core::requireConfig('elections');

class Election {
    public $forms      = null;
    public $panel      = null;
    public $title      = null;
    public $message    = null;
    public $content    = null;
    public $ballot     = null;
    public $candidate  = null;
    public $vote       = null;


    public function adminMenu()
    {
        if (!Current_User::allow('elections')) {
            Current_User::disallow();
        }
        $this->loadPanel();
        $javascript = false;
        $this->loadMessage();

        switch($_REQUEST['aop']) {

            case 'menu':
                if (!isset($_GET['tab'])) {
                    $this->loadForm('list_ballots');
                } else {
                    $this->loadForm($_GET['tab']);
                }
                break;

            case 'new_ballot':
            case 'edit_ballot':
                $this->loadForm('edit_ballot');
                break;
    
            case 'post_ballot':
                if (!Current_User::authorized('elections')) {
                    Current_User::disallow();
                }
                if ($this->postBallot()) {
                    if (PHPWS_Error::logIfError($this->ballot->save())) {
                        $this->forwardMessage(dgettext('elections', 'Error occurred when saving ballot.'));
                        PHPWS_Core::reroute('index.php?module=elections&aop=menu');
                    } else {
                        $this->forwardMessage(dgettext('elections', 'Ballot saved successfully.'));
                        PHPWS_Core::reroute('index.php?module=elections&aop=menu');
                    }
                } else {
                    $this->loadForm('edit_ballot');
                }
                break;
    
            case 'delete_ballot':
                if (!Current_User::authorized('elections')) {
                    Current_User::disallow();
                }
                $this->loadBallot();
                $this->ballot->delete();
                $this->message = dgettext('elections', 'Ballot deleted.');
                $this->loadForm('list');
                break;
                

            case 'edit_candidate':
                $this->loadForm('edit_candidate');
                break;
    
            case 'post_candidate':
                if (!Current_User::authorized('elections')) {
                    Current_User::disallow();
                }
                if ($this->postCandidate()) {
                    if (PHPWS_Error::logIfError($this->candidate->save())) {
                        $this->forwardMessage(dgettext('elections', 'Error occurred when saving candidate.'));
                        PHPWS_Core::reroute('index.php?module=elections&aop=menu');
                    } else {
                        $this->forwardMessage(dgettext('elections', 'Candidate saved successfully.'));
                        PHPWS_Core::reroute('index.php?module=elections&aop=menu');
                    }
                } else {
                    $this->loadForm('edit_candidate');
                }
                break;
    
            case 'delete_candidate':
                if (!Current_User::authorized('elections')) {
                    Current_User::disallow();
                }
                $this->loadCandidate();
                $this->candidate->delete();
                $this->message = dgettext('elections', 'Candidate deleted.');
                $this->loadForm('list');
                break;


            case 'post_settings':
                if (!Current_User::authorized('elections')) {
                    Current_User::disallow();
                }
                if ($this->postSettings()) {
                    $this->forwardMessage(dgettext('elections', 'Election settings saved.'));
                    PHPWS_Core::reroute('index.php?module=elections&aop=menu');
                } else {
                    $this->loadForm('settings');
                }
                break;


            case 'get_report':
                if (!Current_User::authorized('elections')) {
                    Current_User::disallow();
                }
//            print_r($_REQUEST); exit;
                if (isset($_REQUEST['get_results'])) {
                    $this->exportCSV('results', $_REQUEST['results_ballot']);
                } elseif (isset($_REQUEST['list_results'])) {
                    PHPWS_Core::initModClass('elections', 'ELEC_Forms.php');
                    $this->forms = new Elections_Forms;
                    $this->forms->election = & $this;
                    $this->forms->listCandidates($_REQUEST['results_ballot'], 'votes');
                } elseif (isset($_REQUEST['list_candidates'])) {
                    PHPWS_Core::initModClass('elections', 'ELEC_Forms.php');
                    $this->forms = new Elections_Forms;
                    $this->forms->election = & $this;
                    $this->forms->listCandidates($_REQUEST['candidates_ballot']);
                } elseif (isset($_REQUEST['get_votes'])) {
                    $this->exportCSV('votes', $_REQUEST['votes_ballot']);
                } elseif (isset($_REQUEST['list_votes'])) {
                    PHPWS_Core::initModClass('elections', 'ELEC_Forms.php');
                    $this->forms = new Elections_Forms;
                    $this->forms->election = & $this;
                    $this->forms->listVotes($_REQUEST['votes_ballot']);
                } elseif (isset($_REQUEST['purge_votes'])) {
//            print_r($_REQUEST); exit;
                    if ($this->purgeVotes($_REQUEST['votes_ballot'])) {
                        $this->forwardMessage(dgettext('elections', 'Logs successfully purged.'));
                        $this->loadForm('reports');
//                        PHPWS_Core::reroute('index.php?module=elections&aop=menu&tab=reports');
                    } else {
                        $this->forwardMessage(dgettext('elections', 'Error occurred when purging logs.'));
                        $this->loadForm('reports');
                    }
                } else {
                    $this->loadForm('reports');
                }
                break;

        }

        $tpl['TITLE']   = $this->title;
        $tpl['CONTENT'] = $this->content;
        $tpl['MESSAGE'] = $this->message;

        if ($javascript) {
            Layout::nakedDisplay(PHPWS_Template::process($tpl, 'elections', 'main_admin.tpl'));
        } else {
            $this->panel->setContent(PHPWS_Template::process($tpl, 'elections', 'main_admin.tpl'));
            Layout::add(PHPWS_ControlPanel::display($this->panel->display()));
        }
        
   }


    public function userMenu($action=null)
    {
        $javascript = false;
        if (empty($action)) {
            if (!isset($_REQUEST['uop'])) {
                PHPWS_Core::errorPage('404');
            }

            $action = $_REQUEST['uop'];
        }
        $this->loadMessage();

        switch($action) {

            case 'list_ballots':
                PHPWS_Core::initModClass('elections', 'ELEC_Forms.php');
                $this->forms = new Elections_Forms;
                $this->forms->election = & $this;
                $this->forms->listBallots();
                break;
    
            case 'view_ballot':
                $this->loadBallot();
                if (!$this->ballot->pubview && !isset($_SESSION['User']->username)) {
                    $this->content = dgettext('elections', 'This ballot is not available to the general public.');
                } else {
                    Layout::addPageTitle($this->ballot->getTitle());
                    $this->title = $this->ballot->getTitle(true);
                    $this->content = $this->ballot->view();
                }
                break;

            case 'view_candidate':
                $this->loadCandidate();
                Layout::addPageTitle($this->candidate->getTitle());
                $this->title = $this->candidate->getTitle(true, true);
                $this->content = $this->candidate->view();
                break;

            case 'post_vote':
                $this->loadBallot();
                if ($this->ballot->can_vote() == '1') {
                    if ($this->postVote()) {
                        if (PHPWS_Error::logIfError($this->vote->save())) {
                            $this->forwardMessage(dgettext('elections', 'Error occurred when registering vote.'));
                            PHPWS_Core::reroute('index.php?module=elections&uop=list_ballots');
                        } else {
                            $this->forwardMessage(dgettext('elections', 'Vote registered successfully.'));
                            PHPWS_Core::reroute('index.php?module=elections&uop=list_ballots');
                        }
                    } else {
                        $this->loadBallot();
                        if (!$this->ballot->pubview && !isset($_SESSION['User']->username)) {
                            $this->content = dgettext('elections', 'This ballot is not available to the general public.');
                        } else {
                            Layout::addPageTitle($this->ballot->getTitle());
                            $this->title = $this->ballot->getTitle(true);
                            $this->content = $this->ballot->view();
                        }
                    }
                } else {
                    $this->forwardMessage($this->ballot->can_vote());
                    PHPWS_Core::reroute('index.php?module=elections&uop=list_ballots');
                }
                break;

        }

        $tpl['TITLE']   = $this->title;
        $tpl['CONTENT'] = $this->content;
        $tpl['MESSAGE'] = $this->message;

        if ($javascript) {
            Layout::nakedDisplay(PHPWS_Template::process($tpl, 'elections', 'main_user.tpl'));
        } else {
            Layout::add(PHPWS_Template::process($tpl, 'elections', 'main_user.tpl'));
        }
        
   }


    public function sendMessage()
    {
        PHPWS_Core::reroute('index.php?module=elections&amp;uop=message');
    }

    public function forwardMessage($message, $title=null)
    {
        $_SESSION['ELEC_Message']['message'] = $message;
        if ($title) {
            $_SESSION['ELEC_Message']['title'] = $title;
        }
    }
    

    public function loadMessage()
    {
        if (isset($_SESSION['ELEC_Message'])) {
            $this->message = $_SESSION['ELEC_Message']['message'];
            if (isset($_SESSION['ELEC_Message']['title'])) {
                $this->title = $_SESSION['ELEC_Message']['title'];
            }
            PHPWS_Core::killSession('ELEC_Message');
        }
    }


    public function loadForm($type)
    {
        PHPWS_Core::initModClass('elections', 'ELEC_Forms.php');
        $this->forms = new Elections_Forms;
        $this->forms->election = & $this;
        $this->forms->get($type);
    }


    public function loadBallot($id=0)
    {
        PHPWS_Core::initModClass('elections', 'ELEC_Ballot.php');

        if ($id) {
            $this->ballot = new Elections_Ballot($id);
        } elseif (isset($_REQUEST['ballot_id'])) {
            $this->ballot = new Elections_Ballot($_REQUEST['ballot_id']);
        } elseif (isset($_REQUEST['ballot'])) {
            $this->ballot = new Elections_Ballot($_REQUEST['ballot']);
        } elseif (isset($_REQUEST['id'])) {
            $this->ballot = new Elections_Ballot($_REQUEST['id']);
        } else {
            $this->ballot = new Elections_Ballot;
        }
    }


    public function loadCandidate($id=0)
    {
        PHPWS_Core::initModClass('elections', 'ELEC_Candidate.php');

        if ($id) {
            $this->candidate = new Elections_Candidate($id);
        } elseif (isset($_REQUEST['candidate_id'])) {
            $this->candidate = new Elections_Candidate($_REQUEST['candidate_id']);
        } elseif (isset($_REQUEST['candidate'])) {
            $this->candidate = new Elections_Candidate($_REQUEST['candidate']);
        } else {
            $this->candidate = new Elections_Candidate;
        }

        if (empty($this->ballot)) {
            if (isset($this->ballot->id)) {
                $this->loadBallot($this->candidate->ballot_id);
            } else {
                $this->loadBallot();
                $this->candidate->ballot_id = $this->ballot->id;
            }
        }

    }


    public function loadVote($id=0)
    {
        PHPWS_Core::initModClass('elections', 'ELEC_Vote.php');

        if ($id) {
            $this->vote = new Elections_Vote($id);
        } elseif (isset($_REQUEST['vote_id'])) {
            $this->vote = new Elections_Vote($_REQUEST['vote_id']);
        } elseif (isset($_REQUEST['vote'])) {
            $this->vote = new Elections_Vote($_REQUEST['vote']);
        } elseif (isset($_REQUEST['id'])) {
            $this->vote = new Elections_Vote($_REQUEST['id']);
        } else {
            $this->vote = new Elections_Vote;
        }
    }


    public function loadPanel()
    {
        PHPWS_Core::initModClass('controlpanel', 'Panel.php');
        $this->panel = new PHPWS_Panel('elections-panel');
        $link = 'index.php?module=elections&aop=menu';
        
        if (Current_User::isUnrestricted('elections')) {
            $tags['new_ballot'] = array('title'=>dgettext('elections', 'New Ballot'),
                                 'link'=>$link);
            $tags['list_ballots'] = array('title'=>dgettext('elections', 'List Ballots'),
                                  'link'=>$link);
            $tags['new_candidate'] = array('title'=>dgettext('elections', 'New Candidate'),
                                 'link'=>$link);
//            $tags['list_candidates'] = array('title'=>dgettext('elections', 'Candidates'),
//                                  'link'=>$link);
            $tags['reports'] = array('title'=>dgettext('elections', 'Reports'),
                                  'link'=>$link);
            $tags['settings'] = array('title'=>dgettext('elections', 'Settings'),
                                  'link'=>$link);
        }
        if (Current_User::isDeity()) {
            $tags['info'] = array('title'=>dgettext('elections', 'Read me'),
                                 'link'=>$link);
        }
        $this->panel->quickSetTabs($tags);
    }


    public function postBallot()
    {
        $this->loadBallot();

        if (empty($_POST['title'])) {
            $errors[] = dgettext('elections', 'You must give this ballot a title.');
        } else {
            $this->ballot->setTitle($_POST['title']);
        }

        if (empty($_POST['description'])) {
            $errors[] = dgettext('elections', 'You must give this ballot a description.');
        } else {
            $this->ballot->setDescription($_POST['description']);
        }

        if (isset($_POST['image_id'])) {
            $this->ballot->setImage_id((int)$_POST['image_id']);
        }

        isset($_POST['pubview']) ?
            $this->ballot->setPubview(1) :
            $this->ballot->setPubview(0);

        isset($_POST['pubvote']) ?
            $this->ballot->setPubvote(1) :
            $this->ballot->setPubvote(0);

        isset($_POST['show_in_block']) ?
            $this->ballot->setShow_in_block(1) :
            $this->ballot->setShow_in_block(0);

        $this->ballot->setMinchoice($_POST['minchoice']);

        $this->ballot->setMaxchoice($_POST['maxchoice']);

        isset($_POST['ranking']) ?
            $this->ballot->setRanking(1) :
            $this->ballot->setRanking(0);

        if (isset($_POST['votegroups'])) {
            $this->ballot->setVotegroups($_POST['votegroups']);
        } else {
            $this->ballot->setVotegroups(null);
        }

        if (empty($_POST['opening'])) {
            $this->ballot->opening = mktime();
        } else {
            $this->ballot->opening = strtotime($_POST['opening']);
        }

        if (empty($_POST['closing'])) {
            $this->ballot->closing = mktime(0, 0, 0, date("m"), date("d")+PHPWS_Settings::get('elections', 'expiry_interval'), date("Y"));
        } else {
            $this->ballot->closing = strtotime($_POST['closing']);
        }

        if (isset($_POST['custom1label'])) {
            $this->ballot->setCustom1label($_POST['custom1label']);
        }

        if (isset($_POST['custom2label'])) {
            $this->ballot->setCustom2label($_POST['custom2label']);
        }

        if (isset($_POST['custom3label'])) {
            $this->ballot->setCustom3label($_POST['custom3label']);
        }

        if (isset($_POST['custom4label'])) {
            $this->ballot->setCustom4label($_POST['custom4label']);
        }

        if (isset($errors)) {
            $this->message = implode('<br />', $errors);
            $this->loadForm('edit_ballot');
            return false;
        } else {
            return true;
        }

    }


    public function postCandidate()
    {
        $this->loadCandidate();

        if (empty($_POST['title'])) {
            $errors[] = dgettext('elections', 'You must give this candidate a title.');
        } else {
            $this->candidate->setTitle($_POST['title']);
        }

        if (empty($_POST['description'])) {
            $errors[] = dgettext('elections', 'You must give this candidate a description.');
        } else {
            $this->candidate->setDescription($_POST['description']);
        }

        if (isset($_POST['image_id'])) {
            $this->candidate->setImage_id((int)$_POST['image_id']);
        }

        $this->candidate->setBallot_id($_POST['ballot_id']);

        if (empty($this->candidate->ballot_id)) {
            $errors[] = dgettext('elections', 'Fatal error: Cannot create candidate. Missing ballot id.');
        }

        if (isset($_POST['custom1'])) {
            $this->candidate->setCustom1($_POST['custom1']);
        }

        if (isset($_POST['custom2'])) {
            $this->candidate->setCustom2($_POST['custom2']);
        }

        if (isset($_POST['custom3'])) {
            $this->candidate->setCustom3($_POST['custom3']);
        }

        if (isset($_POST['custom4'])) {
            $this->candidate->setCustom4($_POST['custom4']);
        }

        if (isset($errors)) {
            $this->message = implode('<br />', $errors);
            $this->loadForm('edit_candidate');
            return false;
        } else {
            return true;
        }

    }


    public function postSettings()
    {

        isset($_POST['enable_elections']) ?
            PHPWS_Settings::set('elections', 'enable_elections', 1) :
            PHPWS_Settings::set('elections', 'enable_elections', 0);

        isset($_POST['enable_sidebox']) ?
            PHPWS_Settings::set('elections', 'enable_sidebox', 1) :
            PHPWS_Settings::set('elections', 'enable_sidebox', 0);

        isset($_POST['sidebox_homeonly']) ?
            PHPWS_Settings::set('elections', 'sidebox_homeonly', 1) :
            PHPWS_Settings::set('elections', 'sidebox_homeonly', 0);

        if (!empty($_POST['title'])) {
            PHPWS_Settings::set('elections', 'title', strip_tags(PHPWS_Text::parseInput($_POST['title'])));
        } else {
            PHPWS_Settings::reset('elections', 'title');
        }

        if (!empty($_POST['sidebox_text'])) {
            PHPWS_Settings::set('elections', 'sidebox_text', PHPWS_Text::parseInput($_POST['sidebox_text']));
        }

        if (isset($_POST['enable_images'])) {
            PHPWS_Settings::set('elections', 'enable_images', 1);
            if ( !empty($_POST['max_width']) ) {
                $max_width = (int)$_POST['max_width'];
                if ($max_width >= 50 && $max_width <= 600 ) {
                    PHPWS_Settings::set('elections', 'max_width', $max_width);
                }
            }
            if ( !empty($_POST['max_height']) ) {
                $max_height = (int)$_POST['max_height'];
                if ($max_height >= 50 && $max_height <= 600 ) {
                    PHPWS_Settings::set('elections', 'max_height', $max_height);
                }
            }
        } else {
            PHPWS_Settings::set('elections', 'enable_images', 0);
        }

        if ( !empty($_POST['expiry_interval']) ) {
            $expiry_interval = (int)$_POST['expiry_interval'];
            if ($expiry_interval >= 1 && $expiry_interval <= 365 ) {
                PHPWS_Settings::set('elections', 'expiry_interval', $expiry_interval);
            }
        }

        if (isset($errors)) {
            $this->message = implode('<br />', $errors);
            return false;
        } else {
            if (PHPWS_Settings::save('elections')) {
                return true;
            } else { 
                return falsel;
            }
        }

    }


    public function postVote()
    {
        $this->loadVote();

//print_r($_POST['Candidate_Vote']); exit;

        /* make sure of at least 1 vote */
        if (!isset ($_POST['Candidate_Vote']) || !is_array($_POST['Candidate_Vote']) || array_sum($_POST['Candidate_Vote']) < $this->ballot->minchoice) { 
            $errors[] = sprintf(dgettext('elections', 'VOTING ERROR: You must make at least %s selection(s). Please try again.'), $this->ballot->minchoice);

        /* make sure there's not too many */
        } elseif (array_sum($_POST['Candidate_Vote']) > $this->ballot->maxchoice) {
            $errors[] = sprintf(dgettext('elections', 'VOTING ERROR: You are only allowed to make %s selections. Please try again.'), $this->ballot->maxchoice);

        /* OK good to go */
        } else {

//print_r($_POST['Candidate_Vote']); exit;

            PHPWS_Core::initModClass('elections', 'ELEC_Candidate.php');
            /* loop through the Candidate_Vote[] array */
            foreach($_POST['Candidate_Vote'] as $id=>$val) {
                $candidate = new Elections_Candidate($id);
                $old_votes = $candidate->votes;
                $new_votes = $old_votes + $val;
                $candidate->setVotes($new_votes);
                $candidate->save();
                $msgs[] = sprintf(dgettext('elections', 'Your vote has been cast for candidate %s'), $candidate->getTitle());
            }
            
            $this->vote->ballot_id = $_POST['ballot_id'];
            
            if (isset($_SESSION['User']->username)) {
                $this->vote->username = $_SESSION['User']->username;
            } else {
                $this->vote->username  = 'Annon';
            }

            $this->vote->votedate = mktime();
            $this->vote->ip = $_SERVER['REMOTE_ADDR'];


            /* set a cookie for public elections */
//			if ($this->pubvote) {
                $cookieName = "BALLOT_" . $_POST['ballot_id'];
                $value = $_POST['ballot_id'];
                setcookie($cookieName, $value, time()+31536000);
//			}
        }


        if (isset($errors)) {
            $this->message = implode('<br />', $errors);
//            $this->userMenu('view_ballot');
            return false;
        } else {
            $this->message = implode('<br />', $msgs);
            return true;
        }

    }


    public function purgeVotes($ballot_id=0)
    {
        $db = new PHPWS_DB('elections_votes');
        if ($ballot_id > 0) {
            $db->addWhere('ballot_id', $ballot_id);
        }
        return PHPWS_Error::logIfError($db->delete());
    }


    public function exportCSV($type='results', $ballot_id=0) 
    {

        $content = null;

        if ($type == 'results') {
            PHPWS_Core::initModClass('elections', 'ELEC_Candidate.php');
            $content .= Elections_Candidate::printCSVHeader();
            $db = new PHPWS_DB('elections_candidates');
        } elseif ($type == 'votes') {
            PHPWS_Core::initModClass('elections', 'ELEC_Vote.php');
            $content .= Elections_Vote::printCSVHeader();
            $db = new PHPWS_DB('elections_votes');
        }

        $db->addColumn('id');
        if ($ballot_id > 0) {
            $db->addWhere('ballot_id', $ballot_id);
            $db->addOrder('ballot_id', 'asc');
        }

        $db->addOrder('id', 'asc');

        $result = $db->select();

        if ($result) {
            foreach($result as $row) {
                if ($type == 'results') {
                    $item = new Elections_Candidate($row['id']);
                } elseif ($type == 'votes') {
                    $item = new Elections_Vote($row['id']);
                }
                $content .= $item->printCSV();
            }
        }

        if ($type == 'results') {
            if ($ballot_id > 0) {
                $filename = 'ballot_' . $ballot_id . '_results_' . date('Ymd') . '.csv';
            } else {
                $filename = 'elections_results_' . date('Ymd') . '.csv';
            }
        } elseif ($type == 'votes') {
            if ($ballot_id > 0) {
                $filename = 'ballot_' . $ballot_id . '_log_' . date('Ymd') . '.csv';
            } else {
                $filename = 'elections_log_' . date('Ymd') . '.csv';
            }
        }

        Header('Content-Disposition: attachment; filename=' . $filename);
        Header('Content-Length: ' . strlen($content));
        Header('Connection: close');
        Header('Content-Type: text/plain; name=' . $filename);
        echo $content;
        exit();
    }



    public function navLinks()
    {

        $links[] = PHPWS_Text::moduleLink(dgettext('elections', 'List ballots'), 'elections', array('uop'=>'list_ballots'));

        if (Current_User::allow('elections') && !isset($_REQUEST['aop'])){
            $links[] = PHPWS_Text::moduleLink(dgettext('elections', 'Settings'), "elections",  array('aop'=>'menu', 'tab'=>'settings'));
        }
        
        return $links;
    }





}
?>