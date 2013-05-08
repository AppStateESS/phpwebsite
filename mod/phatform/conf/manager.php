<?php

/**
 * Manager Configuration File for PhatForm
 *
 * @version $Id$
 * @author  Steven Levin
 * @modified Matthew McNaney <mcnaney at gmail dot com>
 */

/* Labels */
$id       = dgettext('phatform', 'ID');
$label    = dgettext('phatform', 'Name');
$owner    = dgettext('phatform', 'Owner');
$editor   = dgettext('phatform', 'Editor');
$created  = dgettext('phatform', 'Created');
$updated  = dgettext('phatform', 'Updated');
$hidden   = dgettext('phatform', 'Hidden');
$approved = dgettext('phatform', 'Approved');
$ip       = dgettext('phatform', 'Ip');
$view     = dgettext('phatform', 'View');
$edit     = dgettext('phatform', 'Edit');
$delete   = dgettext('phatform', 'Delete');
$hide     = dgettext('phatform', 'Hide');
$show     = dgettext('phatform', 'Show');
$approve  = dgettext('phatform', 'Approve');
$refuse   = dgettext('phatform', 'Refuse');
$actions  = dgettext('phatform', 'Actions');

$lists = array('saved'=>"approved='1' AND saved='1' AND
                  (archiveTableName is NULL OR archiveTableName = '')",
	       'unsaved'=>"approved='1' AND saved='0' AND 
                  (archiveTableName is NULL OR archiveTableName = '')",
	       'unapproved'=>"approved='0' AND 
                  (archiveTableName is NULL OR archiveTableName = '')",
	       'user'=>"approved='1' AND saved='1' AND hidden='0' AND 
                  (archiveTableName is NULL OR archiveTableName = '')",
	       'options'=>NULL);

$templates = array('saved'     =>'manager',
		   'unsaved'   =>'manager',
		   'unapproved'=>'manager',
		   'user'      =>'manager/user',
		   'options'   =>'options');

/* BEGIN SAVED LIST SETTINGS */
$savedColumns = array('id'     =>$id,
		      'label'  =>$label,
		      'editor' =>$editor,
		      'updated'=>$updated,
		      'hidden' =>$hidden);

$savedActions = array('hide'  =>$hide,
		      'show'  =>$show,
		      'delete'=>$delete);

$savedPermissions = array('hide'  =>NULL,
			  'show'  =>NULL,
			  'delete'=>'delete_forms');

$savedPaging = array('op'     =>'PHAT_MAN_OP=list',
		     'limit'  =>10,
		     'section'=>1,
		     'limits' =>array(5,10,25,50),
		     'back'   =>'&#60;&#60;',
		     'forward'=>'&#62;&#62;');

/* BEGIN UNSAVED LIST SETTINGS */
$unsavedColumns = array('id'     =>$id,
			'label'  =>$label,
			'editor' =>$editor,
			'updated'=>$updated,
			'hidden' =>$hidden);

$unsavedActions = array('delete'=>$delete);

$unsavedPermissions = array('delete'=>'delete_forms');

$unsavedPaging = array('op'     =>'PHAT_MAN_OP=list',
		       'limit'  =>10,
		       'section'=>1,
		       'limits' =>array(5,10,25,50),
		       'back'   =>'&#60;&#60;',
		       'forward'=>'&#62;&#62;');

/* BEGIN UNAPPROVED LIST SETTINGS */
$unapprovedColumns = array('id'     =>$id,
			   'label'  =>$label,
			   'editor' =>$editor,
			   'updated'=>$updated,
			   'hidden' =>$hidden);

$unapprovedActions = array('approve'=>$approve,
			   'refuse' =>$refuse);

$unapprovedPermissions = array('approve'=>'approve_forms',
			       'refuse' =>'approve_forms');

$unapprovedPaging = array('op'     =>'PHAT_MAN_OP=list',
			  'limit'  =>10,
			  'section'=>1,
			  'limits' =>array(5,10,25,50),
			  'back'   =>'&#60;&#60;',
			  'forward'=>'&#62;&#62;');

/* BEGIN USER LIST SETTINGS */
$userColumns = array('id'     =>$id,
		     'label'  =>$label,
		     'updated'=>$updated);

$userActions = array();

$userPermissions = array();

$userPaging = array('op'     =>'PHAT_MAN_OP=list',
		    'limit'  =>10,
		    'section'=>1,
		    'limits' =>array(5,10,25,50),
		    'back'   =>'&#60;&#60;',
		    'forward'=>'&#62;&#62;');

/* BEGIN OPTION LIST SETTINGS */
$optionsColumns = array('id'   =>$id,
			'label'=>$label);

$optionsExtraLabels = array('actions_label'=>$actions,
			    'delete_label' =>$delete,
			    'edit_label'   =>$edit);

?>