CREATE TABLE profiles (
  id int(11) NOT NULL default '0',
  firstname varchar(40) NOT NULL default '',
  lastname varchar(40) NOT NULL default '',
  photo_large int(11) NOT NULL default '0',
  photo_medium int(11) NOT NULL default '0',
  photo_small int(11) NOT NULL default '0',
  fullstory text,
  caption text,
  profile_type smallint(6) NOT NULL default '0',
  keywords text,
  submit_date int(11) NOT NULL default '0',
  contributor varchar(40) NOT NULL default '',
  PRIMARY KEY  (id)
);
