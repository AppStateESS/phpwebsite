CREATE TABLE profiles (
  id int NOT NULL default '0',
  firstname varchar(40) NOT NULL default '',
  lastname varchar(40) NOT NULL default '',
  full_photo int(11) NOT NULL default '0',
  thumbnail int(11) NOT NULL default '0',
  fullstory text,
  caption text,
  profile_type smallint NOT NULL default '0',
  keywords text,
  submit_date int NOT NULL default '0',
  contributor varchar(40) NOT NULL default '',
  PRIMARY KEY  (id)
);
