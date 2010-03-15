<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

// New fields for comments
$fields['avatar']['limit']    = 255; // small graphic of user
$fields['avatar_id']['type']  = 'integer'; // FileCabinet id of small graphic of user
$fields['signature']['limit'] = 255; // graphic or saying from user


// default fields used
$default[] = 'contact_email';
$default[] = 'website';
$default[] = 'location';

?>
