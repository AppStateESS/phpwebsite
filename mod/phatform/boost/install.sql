-- @author Matthew McNaney <mcnaney at gmail dot com>
-- @version $Id$

CREATE TABLE mod_phatform_forms (
  id int unsigned NOT NULL default 0,
  key_id int unsigned NOT NULL default 0,
  owner varchar(20) binary,
  editor varchar(20) binary,
  ip text,
  label text NOT NULL,
  groups mediumtext,
  created int unsigned NOT NULL default 0,
  updated int unsigned NOT NULL default 0,
  hidden smallint NOT NULL default 1,
  approved smallint NOT NULL default 0,
  saved smallint NOT NULL default 0,
  blurb0 mediumtext,
  blurb1 mediumtext,
  elements longtext,
  multiSubmit smallint NOT NULL default 0,
  anonymous smallint NOT NULL default 0,
  editData smallint NOT NULL default 0,
  showElementNumbers smallint NOT NULL default 0,
  showPageNumbers smallint NOT NULL default 0,
  pageLimit int NOT NULL default 20,
  adminEmails text,
  postProcessCode text,
  archiveTableName text default NULL,
  archiveFileName text default NULL,
  PRIMARY KEY  (id)
);

CREATE TABLE mod_phatform_options (
  id int unsigned NOT NULL default 0,
  label text NOT NULL,
  optionSet mediumtext,
  valueSet mediumtext,
  PRIMARY KEY  (id)
);

CREATE TABLE mod_phatform_textfield (
  id int unsigned NOT NULL default 0,
  owner varchar(20) binary,
  editor varchar(20) binary,
  ip text,
  label text NOT NULL,
  groups mediumtext,
  created int unsigned NOT NULL default 0,
  updated int unsigned NOT NULL default 0,
  hidden smallint NOT NULL default 1,
  approved smallint NOT NULL default 0,
  blurb mediumtext,
  value text,
  required smallint NOT NULL default 0,
  size smallint unsigned NOT NULL default 0,
  maxsize smallint unsigned NOT NULL default 0,
  PRIMARY KEY  (id)
);

CREATE TABLE mod_phatform_textarea (
  id int unsigned NOT NULL default 0,
  owner varchar(20) binary,
  editor varchar(20) binary,
  ip text,
  label text NOT NULL,
  groups mediumtext,
  created int unsigned NOT NULL default 0,
  updated int unsigned NOT NULL default 0,
  hidden smallint NOT NULL default 1,
  approved smallint NOT NULL default 0,
  blurb mediumtext,
  value text,
  required smallint NOT NULL default 0,
  rows smallint unsigned NOT NULL default 0,
  cols smallint unsigned NOT NULL default 0,
  PRIMARY KEY  (id)
);

CREATE TABLE mod_phatform_dropbox (
  id int unsigned NOT NULL default 0,
  owner varchar(20) binary,
  editor varchar(20) binary,
  ip text,
  label text NOT NULL,
  groups mediumtext,
  created int NOT NULL default 0,
  updated int NOT NULL default 0,
  hidden smallint NOT NULL default 1,
  approved smallint NOT NULL default 0,
  blurb mediumtext,
  value text,
  required smallint NOT NULL default 0,
  optionText mediumtext,
  optionValues mediumtext,
  optionSet int NOT NULL default 0,
  PRIMARY KEY  (id)
);

CREATE TABLE mod_phatform_multiselect (
  id int unsigned NOT NULL default 0,
  owner varchar(20) binary,
  editor varchar(20) binary,
  ip text,
  label text NOT NULL,
  groups mediumtext,
  created int unsigned NOT NULL default 0,
  updated int unsigned NOT NULL default 0,
  hidden smallint NOT NULL default 1,
  approved smallint NOT NULL default 0,
  blurb mediumtext,
  value text,
  required smallint NOT NULL default 0,
  optionText mediumtext,
  optionValues mediumtext,
  size smallint unsigned NOT NULL default 0,
  optionSet int NOT NULL default 0,
  PRIMARY KEY  (id)
);

CREATE TABLE mod_phatform_radiobutton (
  id int unsigned NOT NULL default 0,
  owner varchar(20) binary,
  editor varchar(20) binary,
  ip text,
  label text NOT NULL,
  groups mediumtext,
  created int unsigned NOT NULL default 0,
  updated int unsigned NOT NULL default 0,
  hidden smallint NOT NULL default 1,
  approved smallint NOT NULL default 0, 
  blurb mediumtext,
  value text,
  required smallint NOT NULL default 0,
  optionText mediumtext,
  optionValues mediumtext,
  optionSet int NOT NULL default 0,
  PRIMARY KEY  (id)
);

CREATE TABLE mod_phatform_checkbox (
  id int unsigned NOT NULL default 0,
  owner varchar(20) binary,
  editor varchar(20) binary,
  ip text,
  label text NOT NULL,
  groups mediumtext,
  created int unsigned NOT NULL default 0,
  updated int unsigned NOT NULL default 0,
  hidden smallint NOT NULL default 1,
  approved smallint NOT NULL default 0,
  blurb mediumtext,
  value text,
  required smallint NOT NULL default 0,
  optionText mediumtext,
  optionValues mediumtext,
  optionSet int NOT NULL default 0,
  PRIMARY KEY  (id)
);

