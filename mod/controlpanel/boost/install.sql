CREATE TABLE mod_controlpanel_link (
  id int NOT NULL default '0',
  owner varchar(20) default '',
  editor varchar(20) default '',
  ip text,
  label text NOT NULL,
  groups text,
  created int NOT NULL default '0',
  updated int NOT NULL default '0',
  hidden smallint NOT NULL default '0',
  approved smallint NOT NULL default '0',
  module text NOT NULL,
  url text NOT NULL,
  description text,
  admin smallint NOT NULL default '0',
  image text,
  PRIMARY KEY(id)
);


CREATE TABLE mod_controlpanel_tab (
  id int NOT NULL default '0',
  owner varchar(20) default '',
  editor varchar(20) default '',
  ip text,
  label text NOT NULL,
  groups text,
  created int NOT NULL default '0',
  updated int NOT NULL default '0',
  hidden smallint NOT NULL default '0',
  approved smallint NOT NULL default '0',
  title varchar(255) default NULL,
  links text,
  grid int NOT NULL default '0',
  taborder int NOT NULL default '0',
  PRIMARY KEY  (id)
);
