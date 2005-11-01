CREATE TABLE webpage_volume (
  id int NOT NULL default '0',
  key_id int NOT NULL default '0',
  title varchar(255) NOT NULL default '',
  summary text,
  date_created int NOT NULL default '0',
  date_updated int NOT NULL default '0',
  created_user varchar(40) NOT NULL default '',
  updated_user varchar(40) NOT NULL default '',
  active smallint NOT NULL default '0',
  restricted smallint NOT NULL default '0',
  frontpage smallint NOT NULL default '0',
  PRIMARY KEY  (id)
);

CREATE TABLE webpage_page (
  id int NOT NULL default '0',
  volume_id int NOT NULL default '0',
  title varchar(255) NULL,
  content text NOT NULL,
  page_number smallint NOT NULL default '0',
  template varchar(40) NOT NULL default '',
  PRIMARY KEY  (id)
);
