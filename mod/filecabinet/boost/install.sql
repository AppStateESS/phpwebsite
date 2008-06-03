-- @author Matthew McNaney <mcnaney at gmail dot com>
-- @version $Id$

CREATE TABLE documents (
  id int unsigned NOT NULL default 0,
  file_name varchar(255) NOT NULL,
  file_directory varchar(255) NOT NULL,
  folder_id int unsigned not null default 0,
  file_type varchar(100) NOT NULL,
  title varchar(255) NOT NULL,
  description text NULL,
  size int unsigned NOT NULL default 0,
  downloaded int unsigned NOT NULL default 0,
  PRIMARY KEY  (id)
);

CREATE TABLE images (
  id int unsigned NOT NULL default 0,
  file_name varchar(255) NOT NULL,
  file_directory varchar(255) NOT NULL,
  folder_id int unsigned not null default 0,
  file_type varchar(30) NOT NULL,
  title varchar(255) NOT NULL,
  description text NULL,
  size int unsigned NOT NULL default 0,
  width smallint unsigned NOT NULL default 0,
  height smallint unsigned NOT NULL default 0,
  url varchar(255) NULL,
  alt varchar(255) NOT NULL,
  PRIMARY KEY  (id)
);

CREATE TABLE multimedia (
  id int unsigned NOT NULL default 0,
  file_name varchar(255) NOT NULL,
  file_directory varchar(255) NOT NULL,
  folder_id int unsigned NOT NULL default 0,
  file_type varchar(30) NOT NULL,
  title varchar(255) NOT NULL,
  description text,
  size int unsigned NOT NULL default 0,
  width smallint unsigned NOT NULL default 0,
  height smallint unsigned NOT NULL default 0,
  thumbnail varchar(255) not null,
  duration int unsigned not null default 0,
  embedded smallint unsigned NOT NULL default 0,
  PRIMARY KEY  (id)
);

CREATE TABLE folders (
  id int unsigned not null default 0,
  key_id int unsigned not null default 0,
  title varchar(60) not null,
  description text null,
  ftype smallint unsigned not null default 1,
  public_folder smallint not null default 1,
  icon varchar(255) not null,
  module_created varchar(40) default null,
  max_image_dimension smallint unsigned not null default 0,
  primary key (id)
);

CREATE TABLE filecabinet_pins (
key_id INT UNSIGNED NOT NULL default 0,
folder_id INT UNSIGNED NOT NULL default 0
);

CREATE TABLE fc_file_assoc (
id INT UNSIGNED NOT NULL default 0,
file_type SMALLINT NOT NULL default 0,
file_id INT UNSIGNED NOT NULL default 0,
resize varchar(30) NULL,
PRIMARY KEY ( id )
);
