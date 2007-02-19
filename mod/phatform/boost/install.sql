-- @author Matthew McNaney <mcnaney at gmail dot com>
-- @version $Id$

CREATE TABLE mod_phatform_forms (
  id int(10) unsigned NOT NULL default 0,
  key_id int NOT NULL default 0,
  owner varchar(20) binary,
  editor varchar(20) binary,
  ip text,
  label text NOT NULL,
  groups mediumtext,
  created int(11) NOT NULL default 0,
  updated int(11) NOT NULL default 0,
  hidden int(1) NOT NULL default 1,
  approved int(1) NOT NULL default 0,
  saved int(1) NOT NULL default 0,
  blurb0 mediumtext,
  blurb1 mediumtext,
  elements longtext,
  multiSubmit int(1) NOT NULL default 0,
  anonymous int(1) NOT NULL default 0,
  editData int(1) NOT NULL default 0,
  showElementNumbers int(1) NOT NULL default 0,
  showPageNumbers int(1) NOT NULL default 0,
  pageLimit int(11) NOT NULL default 20,
  adminEmails text,
  postProcessCode text,
  archiveTableName text default NULL,
  archiveFileName text default NULL,
  PRIMARY KEY  (id)
);

CREATE TABLE mod_phatform_options (
  id int(10) unsigned NOT NULL default 0,
  label text NOT NULL,
  optionSet mediumtext,
  valueSet mediumtext,
  PRIMARY KEY  (id)
);

CREATE TABLE mod_phatform_textfield (
  id int(10) unsigned NOT NULL default 0,
  owner varchar(20) binary,
  editor varchar(20) binary,
  ip text,
  label text NOT NULL,
  groups mediumtext,
  created int(11) NOT NULL default 0,
  updated int(11) NOT NULL default 0,
  hidden int(1) NOT NULL default 1,
  approved int(1) NOT NULL default 0,
  blurb mediumtext,
  value text,
  required int(1) NOT NULL default 0,
  size int(4) NOT NULL default 0,
  maxsize int(4) NOT NULL default 0,
  PRIMARY KEY  (id)
);

CREATE TABLE mod_phatform_textarea (
  id int(10) unsigned NOT NULL default 0,
  owner varchar(20) binary,
  editor varchar(20) binary,
  ip text,
  label text NOT NULL,
  groups mediumtext,
  created int(11) NOT NULL default 0,
  updated int(11) NOT NULL default 0,
  hidden int(1) NOT NULL default 1,
  approved int(1) NOT NULL default 0,
  blurb mediumtext,
  value text,
  required int(1) NOT NULL default 0,
  rows int(4) NOT NULL default 0,
  cols int(4) NOT NULL default 0,
  PRIMARY KEY  (id)
);

CREATE TABLE mod_phatform_dropbox (
  id int(10) unsigned NOT NULL default 0,
  owner varchar(20) binary,
  editor varchar(20) binary,
  ip text,
  label text NOT NULL,
  groups mediumtext,
  created int(11) NOT NULL default 0,
  updated int(11) NOT NULL default 0,
  hidden int(1) NOT NULL default 1,
  approved int(1) NOT NULL default 0,
  blurb mediumtext,
  value text,
  required int(1) NOT NULL default 0,
  optionText mediumtext,
  optionValues mediumtext,
  optionSet int(10) NOT NULL default 0,
  PRIMARY KEY  (id)
);

CREATE TABLE mod_phatform_multiselect (
  id int(10) unsigned NOT NULL default 0,
  owner varchar(20) binary,
  editor varchar(20) binary,
  ip text,
  label text NOT NULL,
  groups mediumtext,
  created int(11) NOT NULL default 0,
  updated int(11) NOT NULL default 0,
  hidden int(1) NOT NULL default 1,
  approved int(1) NOT NULL default 0,
  blurb mediumtext,
  value text,
  required int(1) NOT NULL default 0,
  optionText mediumtext,
  optionValues mediumtext,
  size int(4) NOT NULL default 0,
  optionSet int(10) NOT NULL default 0,
  PRIMARY KEY  (id)
);

CREATE TABLE mod_phatform_radiobutton (
  id int(10) unsigned NOT NULL default 0,
  owner varchar(20) binary,
  editor varchar(20) binary,
  ip text,
  label text NOT NULL,
  groups mediumtext,
  created int(11) NOT NULL default 0,
  updated int(11) NOT NULL default 0,
  hidden int(1) NOT NULL default 1,
  approved int(1) NOT NULL default 0, 
  blurb mediumtext,
  value text,
  required int(1) NOT NULL default 0,
  optionText mediumtext,
  optionValues mediumtext,
  optionSet int(10) NOT NULL default 0,
  PRIMARY KEY  (id)
);

CREATE TABLE mod_phatform_checkbox (
  id int(10) unsigned NOT NULL default 0,
  owner varchar(20) binary,
  editor varchar(20) binary,
  ip text,
  label text NOT NULL,
  groups mediumtext,
  created int(11) NOT NULL default 0,
  updated int(11) NOT NULL default 0,
  hidden int(1) NOT NULL default 1,
  approved int(1) NOT NULL default 0,
  blurb mediumtext,
  value text,
  required int(1) NOT NULL default 0,
  optionText mediumtext,
  optionValues mediumtext,
  optionSet int(10) NOT NULL default 0,
  PRIMARY KEY  (id)
);

