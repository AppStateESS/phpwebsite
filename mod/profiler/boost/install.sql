CREATE TABLE profiles (
  id int NOT NULL default '0',
  firstname varchar(40) NOT NULL default '',
  lastname varchar(40) NOT NULL default '',
  photo_large int NOT NULL default '0',
  photo_medium int NOT NULL default '0',
  photo_small int NOT NULL default '0',
  website varchar(255) NULL default '',
  email varchar(60) NULL default '',
  fullstory text,
  caption text,
  profile_type smallint NOT NULL default '0',
  keywords text,
  submit_date int NOT NULL default '0',
  contributor varchar(40) NOT NULL default '',
  contributor_id int NOT NULL default '0',
  approved smallint NOT NULL default '0',
  PRIMARY KEY  (id)
);


CREATE TABLE profiler_division (
  id int NOT NULL default '0',
  title varchar(60) NOT NULL default '',
  show_homepage smallint NOT NULL default '0',
  PRIMARY KEY  (id)
);
