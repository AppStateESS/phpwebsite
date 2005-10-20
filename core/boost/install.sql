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

CREATE TABLE dependencies (
  source_mod varchar(40) NOT NULL default '',
  depended_on varchar(40) NOT NULL default '',
  version varchar(20) NOT NULL default ''
);

CREATE TABLE mod_settings (
 module varchar(40) NOT NULL,
 setting_name varchar(30) NOT NULL,
 setting_type smallint NOT NULL default '4',
 small_num smallint NULL,
 large_num int NULL,
 small_char varchar(100) NULL,
 large_char text
);

CREATE TABLE phpws_key (
    id INT NOT NULL,
    module varchar(40) NOT NULL,
    item_name char(40) NOT NULL,
    item_id INT NOT NULL,
    title varchar(255),
    url varchar(255),
    active smallint NOT NULL default '1',
    restricted smallint NOT NULL default '0',
    view_permission varchar(30) default NULL,
    edit_permission varchar(30) default NULL,
    PRIMARY KEY (id)
);

