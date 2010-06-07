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

class Elections_Forms {
    public $election = null;

    public function get($type)
    {
        switch ($type) {

            case 'new_ballot':
            case 'edit_ballot':
                if (empty($this->election->ballot)) {
                    $this->election->loadBallot();
                }
                $this->editBallot();
                break;

            case 'list_ballots':
                $this->election->panel->setCurrentTab('list_ballots');
                $this->listBallots();
                break;

            case 'new_candidate':
                $this->selectBallot();
                break;

            case 'edit_candidate':
                if (empty($this->election->candidate)) {
                    $this->election->loadCandidate();
                }
                $this->editCandidate();
                break;

            case 'list_candidates':
                $this->election->panel->setCurrentTab('list_candidates');
                $this->listCandidates();
                break;

            case 'reports':
                $this->election->panel->setCurrentTab('reports');
                $this->reports();
                break;

            case 'settings':
                $this->election->panel->setCurrentTab('settings');
                $this->editSettings();
                break;

            case 'info':
                $this->election->panel->setCurrentTab('info');
                $this->showInfo();
                break;

        }

    }


    public function listBallots()
    {
        if (PHPWS_Settings::get('elections', 'enable_elections') || Current_User::isUnrestricted('elections')) {
            $ptags['TITLE_HEADER'] = dgettext('elections', 'Title');
            $ptags['OPENS_HEADER'] = dgettext('elections', 'Opens');
            $ptags['CLOSES_HEADER'] = dgettext('elections', 'Closes');
            $ptags['CANDIDATES_HEADER'] = dgettext('elections', 'Candidates');
            if (Current_User::isUnrestricted('elections')) {
                $ptags['VOTES_HEADER'] = dgettext('elections', 'Votes');
            }

            Core\Core::initModClass('elections', 'ELEC_Ballot.php');
            Core\Core::initCoreClass('DBPager.php');
            $pager = new DBPager('elections_ballots', 'Elections_Ballot');
            $pager->setModule('elections');
            if (!isset($_SESSION['User']->username)) {
                $pager->addWhere('pubview', 1);
            }
            $pager->setOrder('title', 'asc', true);
            $pager->setTemplate('list_ballots.tpl');
            $pager->addRowTags('rowTag');
            $num = $pager->getTotalRows();
            if ($num == '0' && Current_User::isUnrestricted('elections')) {
                $vars['aop']  = 'menu';
                $vars['tab']  = 'settings';
                $vars2['aop']  = 'new_ballot';
                $ptags['EMPTY_MESSAGE'] = sprintf(dgettext('elections', 'Check your %s then create a %s to begin'), PHPWS_Text::secureLink(dgettext('elections', 'Settings'), 'elections', $vars),  PHPWS_Text::secureLink(dgettext('elections', 'New Ballot'), 'elections', $vars2));
            }
            $pager->addPageTags($ptags);
            $pager->addToggle('class="toggle1"');
            $pager->setSearch('title', 'description');

            $content = $pager->get();
        } else {
            $content = dgettext('elections', 'Thank you for your interest. However, all elections are currently closed.');
        }

        $this->election->content = $content;
        $this->election->title = sprintf(dgettext('elections', '%s Ballots'), PHPWS_Text::parseOutput(PHPWS_Settings::get('elections', 'title')));
    }


    public function listCandidates($ballot_id=0, $sort='title')
    {
        $ptags['TITLE_HEADER'] = dgettext('elections', 'Name');
        $ptags['BALLOT_HEADER'] = dgettext('elections', 'Ballot');
        $ptags['VOTES_HEADER'] = dgettext('elections', 'Votes');

        Core\Core::initModClass('elections', 'ELEC_Candidate.php');
        Core\Core::initCoreClass('DBPager.php');
        $pager = new DBPager('elections_candidates', 'Elections_Candidate');
        $pager->setModule('elections');

        if ($ballot_id > 0) {
            $pager->addWhere('ballot_id', $ballot_id);
        }
        if ($sort == 'title') {
            $pager->setOrder('title', 'asc', true);
        } elseif ($sort == 'votes') {
            $pager->setOrder('votes', 'desc', true);
        }
        $pager->setTemplate('list_candidates.tpl');
        $pager->addRowTags('rowTag');
        $num = $pager->getTotalRows();
        if ($num == '0') {
            $vars['aop']  = 'menu';
            $vars['tab']  = 'settings';
            $vars2['aop']  = 'menu';
            $vars2['tab']  = 'new_candidate';
            $ptags['EMPTY_MESSAGE'] = sprintf(dgettext('elections', 'Check your %s then create a %s to begin'), PHPWS_Text::secureLink(dgettext('elections', 'Settings'), 'elections', $vars),  PHPWS_Text::secureLink(dgettext('elections', 'New Candidate'), 'elections', $vars2));
        }
        $pager->addPageTags($ptags);
        $pager->addToggle('class="toggle1"');
        $pager->setSearch('title', 'description');

        $this->election->content = $pager->get();
        $this->election->title = sprintf(dgettext('elections', '%s Candidates'), PHPWS_Text::parseOutput(PHPWS_Settings::get('elections', 'title')));
    }


    public function listVotes($ballot_id=0)
    {
        if (Current_User::isUnrestricted('elections')) {
            $ptags['BALLOT_HEADER'] = dgettext('elections', 'Ballot');
            $ptags['USER_HEADER'] = dgettext('elections', 'Username');
            $ptags['DATE_HEADER'] = dgettext('elections', 'Vote date');
            $ptags['IP_HEADER'] = dgettext('elections', 'IP Address');

            Core\Core::initModClass('elections', 'ELEC_Vote.php');
            Core\Core::initCoreClass('DBPager.php');
            $pager = new DBPager('elections_votes', 'Elections_Vote');
            $pager->setModule('elections');
            if ($ballot_id > 0) {
                $pager->addWhere('ballot_id', $ballot_id);
            }

            $pager->setOrder('votedate', 'desc', true);
            $pager->setTemplate('list_votes.tpl');
            $pager->addRowTags('rowTag');

            $pager->addPageTags($ptags);
            $pager->addToggle('class="toggle1"');
            $pager->setSearch('username', 'ip');

            $content = $pager->get();
        } else {
            $content = dgettext('elections', 'This is a restricted area.');
        }

        $this->election->content = $content;
        $this->election->title = sprintf(dgettext('elections', '%s Voting Log'), PHPWS_Text::parseOutput(PHPWS_Settings::get('elections', 'title')));
    }


    public function editBallot()
    {
        $form = new PHPWS_Form('elections_ballot');
        $ballot = & $this->election->ballot;
        $choices = array('1'=>1,'2'=>2,'3'=>3,'4'=>4,'5'=>5,'6'=>6,'7'=>7,'8'=>8,'9'=>9,'10'=>10,'11'=>11,'12'=>12,'13'=>13,'14'=>14,'15'=>15,'16'=>16,'17'=>17,'18'=>18,'19'=>19,'20'=>20);

        $form->addHidden('module', 'elections');
        $form->addHidden('aop', 'post_ballot');
        if ($ballot->id) {
            $form->addHidden('id', $ballot->id);
            $form->addSubmit(dgettext('elections', 'Update'));
            $this->election->title = dgettext('elections', 'Update elections ballot');
        } else {
            $form->addSubmit(dgettext('elections', 'Create'));
            $this->election->title = dgettext('elections', 'Create elections ballot');
        }

        $form->addText('title', $ballot->getTitle());
        $form->setSize('title', 40);
        $form->setLabel('title', dgettext('elections', 'Title'));

        $form->addTextArea('description', $ballot->getDescription());
        $form->useEditor('description', true, true, 0, 0, 'tinymce');
        $form->setRows('description', '6');
        $form->setCols('description', '40');
        $form->setLabel('description', dgettext('elections', 'Description'));


        Core\Core::initModClass('filecabinet', 'Cabinet.php');
        $manager = Cabinet::fileManager('image_id', $ballot->image_id);
        $manager->imageOnly();
        $manager->maxImageWidth(PHPWS_Settings::get('elections', 'max_width'));
        $manager->maxImageHeight(PHPWS_Settings::get('elections', 'max_height'));
        if ($manager) {
            $form->addTplTag('FILE_MANAGER', $manager->get());
        }

        $form->addCheckbox('pubview', 1);
        $form->setMatch('pubview', $ballot->pubview);
        $form->setLabel('pubview', dgettext('elections', 'Allow public to view this ballot'));

        $form->addCheckbox('pubvote', 1);
        $form->setMatch('pubvote', $ballot->pubvote);
        $form->setLabel('pubvote', dgettext('elections', 'Allow public to vote in this election'));

        $form->addCheckbox('show_in_block', 1);
        $form->setMatch('show_in_block', $ballot->show_in_block);
        $form->setLabel('show_in_block', dgettext('elections', 'List in sidebox (if sidebox is set to visible in Elections settings).'));

        $form->addSelect('minchoice', $choices);
        $form->setMatch('minchoice', $ballot->minchoice);
        $form->setLabel('minchoice', dgettext('elections', 'Minimum # of selections a voter may mark on this ballot'));

        $form->addSelect('maxchoice', $choices);
        $form->setMatch('maxchoice', $ballot->maxchoice);
        $form->setLabel('maxchoice', dgettext('elections', 'Maximum # of selections a voter may mark on this ballot'));

        $form->addCheckbox('ranking', 1);
        $form->setMatch('ranking', $ballot->ranking);
        $form->setLabel('ranking', dgettext('elections', 'Enable candidate ranking instead of simple selection'));

        $form->addText('opening', $ballot->getOpening('%Y/%m/%d %H:%M'));
        $form->setLabel('opening', dgettext('elections', 'Opening date/time'));
        $form->setSize('opening', 20);

        $form->addText('closing', $ballot->getClosing('%Y/%m/%d %H:00'));
        $form->setLabel('closing', dgettext('elections', 'Closing date/time'));
        $form->setSize('closing', 20);

        $form->addText('custom1label', $ballot->getCustom1label());
        $form->setSize('custom1label', 40);
        $form->setLabel('custom1label', dgettext('elections', 'Custom Field 1 Label'));

        $form->addText('custom2label', $ballot->getCustom2label());
        $form->setSize('custom2label', 40);
        $form->setLabel('custom2label', dgettext('elections', 'Custom Field 2 Label'));

        $form->addText('custom3label', $ballot->getCustom3label());
        $form->setSize('custom3label', 40);
        $form->setLabel('custom3label', dgettext('elections', 'Custom Field 3 Label'));

        $form->addText('custom4label', $ballot->getCustom4label());
        $form->setSize('custom4label', 40);
        $form->setLabel('custom4label', dgettext('elections', 'Custom Field 4 Label'));

        $tpl = $form->getTemplate();

        $tpl['DETAILS_LABEL'] = dgettext('elections', 'Details');

        if ($ballot->votegroups) {
            $match = explode(":", $ballot->votegroups);
        } else {
            $match = null;
        }
        $tpl['VOTEGROUPS'] = $this->getGroupsSelect($match, 'votegroups', true, true);
        $tpl['VOTEGROUPS_LABEL'] = dgettext('elections', 'If you wish to restrict voting on this ballot to a particular group of members, choose the group(s) you wish to restrict access to. Press CTRL and Click to select multiple groups, press CTRL and Click again to deselect an option.');

        $jscal['form_name'] = 'elections_ballot';
        $jscal['type']      = 'text_clock';

        $jscal['date_name'] = 'opening';
        $tpl['OPENING_CAL'] = javascript('js_calendar', $jscal);

        $jscal['date_name'] = 'closing';
        $tpl['CLOSING_CAL'] = javascript('js_calendar', $jscal);

        $tpl['EXAMPLE'] = 'YY/MM/DD HH:MM';

        $tpl['CUSTOMFIELDS_TEXT'] = dgettext('elections', 'Custom Fields');
        $tpl['CUSTOMFIELDS_NOTE'] = dgettext('elections', 'Using these will add fields to the candidate template for this election.');

        $this->election->content = PHPWS_Template::process($tpl, 'elections', 'edit_ballot.tpl');
    }


    public function editCandidate()
    {
        $form = new PHPWS_Form;
        $candidate = & $this->election->candidate;
        $ballot = & $this->election->ballot;

        $form->addHidden('module', 'elections');
        $form->addHidden('aop', 'post_candidate');
        $form->addHidden('ballot_id', $ballot->id);
        if ($candidate->id) {
            $this->election->title = sprintf(dgettext('elections', 'Update %s candidate'), $ballot->title);
            $form->addHidden('candidate_id', $candidate->id);
            $form->addSubmit(dgettext('elections', 'Update'));
        } else {
            $this->election->title = sprintf(dgettext('elections', 'Add candidate to %s'), $ballot->title);
            $form->addSubmit(dgettext('elections', 'Add'));
        }

        $form->addText('title', $candidate->title);
        $form->setSize('title', 40);
        $form->setLabel('title', dgettext('elections', 'Title'));

        $form->addTextArea('description', $candidate->description);
        $form->setRows('description', '6');
        $form->setCols('description', '40');
        $form->setLabel('description', dgettext('elections', 'Description'));

        Core\Core::initModClass('filecabinet', 'Cabinet.php');
        $manager = Cabinet::fileManager('image_id', $candidate->image_id);
        $manager->imageOnly();
        $manager->maxImageWidth(PHPWS_Settings::get('elections', 'max_width'));
        $manager->maxImageHeight(PHPWS_Settings::get('elections', 'max_height'));
        if ($manager) {
            $form->addTplTag('FILE_MANAGER', $manager->get());
        }

        if ($ballot->custom1label) {
            $form->addText('custom1', $candidate->custom1);
            $form->setSize('custom1', 40);
            $form->setLabel('custom1', $ballot->custom1label);
        }

        if ($ballot->custom2label) {
            $form->addText('custom2', $candidate->custom2);
            $form->setSize('custom2', 40);
            $form->setLabel('custom2', $ballot->custom2label);
        }

        if ($ballot->custom3label) {
            $form->addText('custom3', $candidate->custom3);
            $form->setSize('custom3', 40);
            $form->setLabel('custom3', $ballot->custom3label);
        }

        if ($ballot->custom4label) {
            $form->addText('custom4', $candidate->custom4);
            $form->setSize('custom4', 40);
            $form->setLabel('custom4', $ballot->custom4label);
        }

        $tpl = $form->getTemplate();
        $tpl['INFO_LABEL'] = dgettext('elections', 'Profile');
        if ($ballot->custom1label || $ballot->custom2label || $ballot->custom3label || $ballot->custom4label) {
            $tpl['CUSTOM_LABEL'] = dgettext('elections', 'Details');
        }

        $this->election->content = PHPWS_Template::process($tpl, 'elections', 'edit_candidate.tpl');
    }


    public function reports()
    {

        $form = new PHPWS_Form('elections_reports');
        $form->addHidden('module', 'elections');
        $form->addHidden('aop', 'get_report');
        $form->setMethod('get');
        $tpl = $form->getTemplate();

        $tpl['RESULTS_TITLE'] = dgettext('elections', 'Results');
        $tpl['RESULTS_INFO'] = dgettext('elections', 'Choose ALL results, or by ballot. Nummber in parenthesis represents ballots cast.');
        $tpl['RESULTS'] = $this->getBallotsSelect(null, 'results_ballot', false, true, 'elections_votes');
        $tpl['EXPORT_RESULTS_BUTTON'] = $this->getSubmitButton('get_results');
        $tpl['LIST_RESULTS_BUTTON'] = $this->getSubmitButton('list_results');

        $tpl['CANDIDATES_TITLE'] = dgettext('elections', 'Candidates');
        $tpl['CANDIDATES_INFO'] = dgettext('elections', 'Choose ALL candidates, or by ballot. Nummber in parenthesis represents number of candidates in ballot.');
        $tpl['CANDIDATES'] = $this->getBallotsSelect(null, 'candidates_ballot', false, true, 'elections_candidates');
        $tpl['CANDIDATES_BUTTON'] = $this->getSubmitButton('list_candidates');

        $tpl['VOTES_TITLE'] = dgettext('elections', 'Voting log');
        $tpl['VOTES_INFO'] = dgettext('elections', 'Choose ALL records, or by ballot. Nummber in parenthesis represents ballots cast.');
        $tpl['VOTES'] = $this->getBallotsSelect(null, 'votes_ballot', false, true, 'elections_votes');
        $tpl['EXPORT_VOTES_BUTTON'] = $this->getSubmitButton('get_votes');
        $tpl['LIST_VOTES_BUTTON'] = $this->getSubmitButton('list_votes');
        $tpl['PURGE_VOTES_BUTTON'] = $this->getSubmitButton('purge_votes');

        javascriptMod('elections', 'utilities');

        $this->election->title = dgettext('elections', 'Reports');
        $this->election->content = PHPWS_Template::process($tpl, 'elections', 'reports.tpl');
    }


    public function editSettings()
    {

        $form = new PHPWS_Form('elections_settings');
        $form->addHidden('module', 'elections');
        $form->addHidden('aop', 'post_settings');

        $form->addCheckbox('enable_elections', 1);
        $form->setMatch('enable_elections', PHPWS_Settings::get('elections', 'enable_elections'));
        $form->setLabel('enable_elections', dgettext('elections', 'Enable elections'));

        $form->addCheckbox('enable_sidebox', 1);
        $form->setMatch('enable_sidebox', PHPWS_Settings::get('elections', 'enable_sidebox'));
        $form->setLabel('enable_sidebox', dgettext('elections', 'Enable elections sidebox'));

        $form->addCheckbox('sidebox_homeonly', 1);
        $form->setMatch('sidebox_homeonly', PHPWS_Settings::get('elections', 'sidebox_homeonly'));
        $form->setLabel('sidebox_homeonly', dgettext('elections', 'Show sidebox on home page only'));

        $form->addTextField('title', PHPWS_Settings::get('elections', 'title'));
        $form->setLabel('title', dgettext('elections', 'Module title'));
        $form->setSize('title', 30);

        $form->addTextArea('sidebox_text', PHPWS_Settings::get('elections', 'sidebox_text'));
        $form->setRows('sidebox_text', '4');
        $form->setCols('sidebox_text', '40');
        $form->setLabel('sidebox_text', dgettext('elections', 'Sidebox text'));

        $form->addCheckbox('enable_images', 1);
        $form->setMatch('enable_images', PHPWS_Settings::get('elections', 'enable_images'));
        $form->setLabel('enable_images', dgettext('elections', 'Enable images on candidate profiles'));

        $form->addTextField('max_width', PHPWS_Settings::get('elections', 'max_width'));
        $form->setLabel('max_width', dgettext('elections', 'Maximum image width (50-600)'));
        $form->setSize('max_width', 4,4);

        $form->addTextField('max_height', PHPWS_Settings::get('elections', 'max_height'));
        $form->setLabel('max_height', dgettext('elections', 'Maximum image height (50-600)'));
        $form->setSize('max_height', 4,4);

        $form->addTextField('expiry_interval', PHPWS_Settings::get('elections', 'expiry_interval'));
        $form->setLabel('expiry_interval', dgettext('elections', 'Default open time of election (in days, 1-365)'));
        $form->setSize('expiry_interval', 4,4);

        $form->addSubmit('save', dgettext('elections', 'Save settings'));

        $tpl = $form->getTemplate();
        $tpl['SETTINGS_LABEL'] = dgettext('elections', 'General Settings');

        $this->election->title = dgettext('elections', 'Settings');
        $this->election->content = PHPWS_Template::process($tpl, 'elections', 'edit_settings.tpl');
    }


    public function showInfo()
    {

        $filename = 'mod/elections/docs/README';
        if (@fopen($filename, "rb")) {
            $handle = fopen($filename, "rb");
            $readme = fread($handle, filesize($filename));
            fclose($handle);
        } else {
            $readme = dgettext('elections', 'Sorry, the readme file does not exist.');
        }

        $tpl['TITLE'] = dgettext('elections', 'Important Information');
        $tpl['INFO'] = $readme;
        $tpl['DONATE'] = sprintf(dgettext('elections', 'If you would like to help out with the ongoing development of elections, or other modules by Verdon Vaillancourt, %s click here to donate %s (opens in new browser window).'), '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=donations%40verdon%2eca&item_name=Elections%20Module%20Development&no_shipping=0&no_note=1&tax=0&currency_code=USD&lc=CA&bn=PP%2dDonationsBF&charset=UTF%2d8" target="new">', '</a>');

        $this->election->title = dgettext('elections', 'Read me');
        $this->election->content = PHPWS_Template::process($tpl, 'elections', 'info.tpl');
    }


    public function selectBallot()
    {

        $form = new PHPWS_Form('elections_ballots');
        $form->addHidden('module', 'elections');
        $form->addHidden('aop', 'edit_candidate');

        $result = $this->getAllBallots();

        if ($result) {
            foreach ($result as $ballot) {
                $choices[$ballot->id] = $ballot->title;
            }
            $form->addSelect('ballot_id', $choices);
            $form->setLabel('ballot_id', dgettext('elections', 'Available ballots'));
            $form->addSubmit('save', dgettext('elections', 'Continue'));
        } else {
            $form->addTplTag('NO_BALLOTS_NOTE', dgettext('elections', 'Sorry, there are no ballots available. You will have to create a ballot first.'));
        }

        $tpl = $form->getTemplate();
        $tpl['BALLOT_ID_GROUP_LABEL'] = dgettext('elections', 'Select ballot');

        $this->election->title = dgettext('elections', 'New candidate step one');
        $this->election->content = PHPWS_Template::process($tpl, 'elections', 'select_ballot.tpl');
    }


    public function getAllGroups()
    {
        Core\Core::initModClass('users', 'Action.php');
        return User_Action::getGroups('group');
    }


    public function getGroupsSelect($match=null, $select_name='votegroups', $multiple=true, $count=false)
    {

        $groups = $this->getAllGroups();

        if ($groups) {
            foreach ($groups as $var=>$val) {
                if ($count) {
                    $db = new PHPWS_DB('users_members');
                    $db->addWhere('group_id', $var);
                    $qty = $db->count();
                    $items[$var] = $val . ' ('.$qty.')';
                } else {
                    $items[$var] = $val;
                }
            }
        }

        if ($items) {
            if ($multiple) {
                $form = new PHPWS_Form;
                $form->addMultiple($select_name, $items);
                if (!empty($match) && is_array($match)) {
                    $form->setMatch($select_name, $match);
                }
                return $form->get($select_name);
            } else {
                $form = new PHPWS_Form;
                $form->addSelect($select_name, $items);
                if (!empty($match) && is_string($match)) {
                    $form->setMatch($select_name, $match);
                }
                return $form->get($select_name);
            }
        } else {
            return dgettext('elections', 'No groups configured.');
        }

    }


    public function getAllBallots()
    {
        Core\Core::initModClass('elections', 'ELEC_Ballot.php');
        $db = new PHPWS_DB('elections_ballots');
        $db->addColumn('id');
        $db->addColumn('title');
        $result = $db->getObjects('Elections_Ballot');
//        print_r($result); exit;
        
        return $result;
    }


    public function getBallotsSelect($match=null, $select_name='ballot', $multiple=true, $count=false, $countitem='elections_votes')
    {

        $ballots = $this->getAllBallots();

        if ($ballots) {
            $items[0] = dgettext('elections', '- All -');
            foreach ($ballots as $ballot) {
                if ($count) {
                    $db = new PHPWS_DB($countitem);
                    $db->addWhere('ballot_id', $ballot->id);
                    $qty = $db->count();
                    $items[$ballot->id] = $ballot->title . ' ('.$qty.')';
                } else {
                    $items[$ballot->id] = $ballot->title;
                }
            }
        }

        if (!empty($items)) {
            $form = new PHPWS_Form;
            if ($multiple) {
                $form->addMultiple($select_name, $items);
                if (!empty($match) && is_array($match)) {
                    $form->setMatch($select_name, $match);
                }
                return $form->get($select_name);
            } else {
                $form = new PHPWS_Form;
                $form->addSelect($select_name, $items);
                if (!empty($match) && is_string($match)) {
                    $form->setMatch($select_name, $match);
                }
                return $form->get($select_name);
            }
        } else {
            return dgettext('elections', 'No ballots created.');
        }

    }


    public function getSubmitButton($type='get_results')
    {
        $extra = false;
        if ($type == 'get_results') {
            $text = dgettext('elections', 'Export voting results');
        } elseif ($type == 'list_results') {
            $text = dgettext('elections', 'List voting results');
        } elseif ($type == 'list_candidates') {
            $text = dgettext('elections', 'List candidates by ballot');
        } elseif ($type == 'get_votes') {
            $text = dgettext('elections', 'Export voting log');
        } elseif ($type == 'list_votes') {
            $text = dgettext('elections', 'List voting log');
        } elseif ($type == 'purge_votes') {
            $text = dgettext('elections', 'Purge voting log');
            $extra = true;
        } else {
            $text = dgettext('elections', 'Submit');
        }
        $form = new PHPWS_Form;
        $form->addSubmit($type, $text);
        if ($extra) {
            $question = dgettext('elections', 'Are you sure you wish to purge the selected logs');
            $form->setExtra($type, 'onclick="this.value=\'' . $text . '\'; return confirmAction(\'' . $question . '\', this.form)"');
        }
        return $form->get($type);
    }




}

?>