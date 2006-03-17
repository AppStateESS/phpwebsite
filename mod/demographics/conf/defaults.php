<?php

  /**
   * Default demographics fields
   *
   * It is assumed a developer will use fields from the list before reinventing
   * fields for their module.
   *
   * Module field files should be structured just like this file.
   * Each field can have the following charactistics
   * 
   *    type  - either character, smallint, integer or boolean. if not set, 
   *            defaults to char
   *    limit - maximum characters allowed. Defaults to 100. 255 is the maximum
   *            limits. (Some databases go over this, some don't)
   *            Ignored if type is boolean.
   *
   * The index of the array is the label for the field
   *
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

  /* ----- Personal information ----- */
$fields['first_name']['limit'] = 40;
$fields['middle_name']['limit'] = 40;
$fields['last_name']['limit'] = 40;
$fields['middle_initial']['limit'] = 1;

// wouldn't suggest asking for this but left here for
// completeness sake
$fields['social_security']['limit'] = 9;

// one character (m)ale, (f)emale or (o)ther/(t)rans
$fields['sex']['limit'] = 1;
$fields['age']['type'] = 'smallint';
$fields['birthday']['type'] = 'smallint'; // format YYYYMMDD

/* ----- Contact information ----- */
$fields['day_phone']['limit']     = 20;
$fields['evening_phone']['limit'] = 20;
$fields['fax_number']['limit']    = 20;
$fields['mobile_phone']['limit']  = 20;


/* ----- Internet infomation ----- */
$fields['permanent_email']['limit'] = 30;
$fields['contact_email']['limit']   = 30;
$fields['website']['limit']         = 60;
$fields['aim_id']['limit']          = 20; // AOL messenger
$fields['icq_id']['limit']          = 20; // ICQ messenger
$fields['msm_id']['limit']          = 20; // Microsoft messenger
$fields['irc']['limit']             = 50; // IRC server and channel


/* ----- Addresses ----- */
$fields['mailing_address_1']['limit']   = 50;
$fields['mailing_address_2']['limit']   = 50;
$fields['mailing_address_3']['limit']   = 50;
$fields['mailing_city']['limit']        = 20;
$fields['mailing_state']['limit']       = 20;
$fields['mailing_county']['limit']      = 20;
$fields['mailing_country']['limit']     = 30;
$fields['mailing_zip_code']['limit']    = 10;
$fields['mailing_postal_code']['limit'] = 10;

$fields['business_address_1']['limit']   = 50;
$fields['business_address_2']['limit']   = 50;
$fields['business_address_3']['limit']   = 50;
$fields['business_city']['limit']        = 20;
$fields['business_state']['limit']       = 20;
$fields['business_county']['limit']      = 20;
$fields['business_country']['limit']     = 30;
$fields['business_zip_code']['limit']    = 10;
$fields['business_postal_code']['limit'] = 10;

// Some users may have an address where they can
// always be reached. The mailing address may be temporary
$fields['permanent_address_1']['limit']   = 50;
$fields['permanent_address_2']['limit']   = 50;
$fields['permanent_address_3']['limit']   = 50;
$fields['permanent_city']['limit']        = 20;
$fields['permanent_state']['limit']       = 20;
$fields['permanent_county']['limit']      = 20;
$fields['permanent_country']['limit']     = 30;
$fields['permanent_zip_code']['limit']    = 10;
$fields['permanent_postal_code']['limit'] = 10;

?>