<?php
/**
    * rolodex - phpwebsite module
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


  // New fields for rolodex
$fields['tollfree_phone']['limit'] = 20; 
$fields['business_name']['limit'] = 255; 


// default fields used

/* ----- Personal information ----- */
$default[] = 'courtesy_title';
$default[] = 'honorific';
$default[] = 'first_name';
$default[] = 'last_name';
$default[] = 'middle_initial';
$default[] = 'department';
$default[] = 'position_title';

/* ----- Contact information ----- */
$default[] = 'day_phone';
$default[] = 'day_phone_ext';
$default[] = 'evening_phone';
$default[] = 'fax_number';

/* ----- Internet infomation ----- */
$default[] = 'contact_email';
$default[] = 'website';

/* ----- Addresses ----- */
$default[] = 'mailing_address_1';
$default[] = 'mailing_address_2';
$default[] = 'mailing_city';
$default[] = 'mailing_state';
$default[] = 'mailing_country';
$default[] = 'mailing_zip_code';

$default[] = 'business_address_1';
$default[] = 'business_address_2';
$default[] = 'business_city';
$default[] = 'business_state';
$default[] = 'business_country';
$default[] = 'business_zip_code';


?>