<?php

/**
 * Generic Class for DB loading and saving, DB Paging, etc.
 * Do not instantiate this class for rendering the tracker; instead,
 * use TrackerFactory to instantiate the particular Tracker class.
 *
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('analytics', 'Tracker.php');

class GenericTracker extends Tracker
{
    public function track()
    {
        throw new Exception('Cannot track with the GenericTracker.');
    }

    public function trackerType()
    {
        return 'GenericTracker';
    }

    public function addForm(PHPWS_Form &$form)
    {
        throw new Exception('Cannot create or edit a GenericTracker.');
    }

    public function joinDb(PHPWS_DB &$db)
    {
        throw new Exception('Cannot load a GenericTracker like that.');
    }

    public function getFormTemplate()
    {
        throw new Exception('Cannot create or edit a GenericTracker.');
    }

    public function getPagerTags()
    {
        $template['NAME'] = $this->name;
        $template['TYPE'] = $this->type;
        $template['ACTIVE'] = $this->active ? 'Active' : 'Inactive';
        $template['ACTION'] = 'Herp';

        $actions = array();
        $actions[] = PHPWS_Text::secureLink('Edit', 'analytics',
            array('command'=>'edit','tracker_id'=>$this->id));
        $actions[] = PHPWS_Text::secureLink('Delete', 'analytics',
            array('command'=>'delete','tracker_id'=>$this->id));

        $template['ACTION'] = implode(' | ', $actions);
        
        return $template;
    }
}

?>
