CREATE TABLE modules ( 
	title CHAR(40) NOT NULL, 
	proper_name CHAR(40) NOT NULL,
	priority SMALLINT NOT NULL, 
	active SMALLINT NOT NULL, 
	version CHAR(20) NOT NULL, 
	register SMALLINT NOT NULL,
	unregister SMALLINT NOT NULL,
	pre94 SMALLINT NOT NULL
	);

CREATE TABLE registered ( 
	module CHAR(40) NOT NULL, 
	registered CHAR(40) NOT NULL
	);

CREATE TABLE documents (
  id int NOT NULL default '0',
  filename varchar(50) NOT NULL default '',
  directory varchar(150) NOT NULL default '',
  type varchar(30) NOT NULL default '',
  title varchar(255) NOT NULL default '',
  description text NOT NULL,
  size int NOT NULL default '0',
  module varchar(40) default NULL,
  PRIMARY KEY  (id)
);

CREATE TABLE images (
  id int NOT NULL default '0',
  filename varchar(50) NOT NULL default '',
  directory varchar(150) NOT NULL default '',
  type varchar(30) NOT NULL default '',
  title varchar(255) NOT NULL default '',
  description text,
  size int NOT NULL default '0',
  width smallint NOT NULL default '0',
  height smallint NOT NULL default '0',
  alt varchar(255) NOT NULL default '',
  thumbnail_source int NOT NULL default '0',
  module varchar(40) default NULL,
  PRIMARY KEY  (id)
);
