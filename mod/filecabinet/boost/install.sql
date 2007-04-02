-- @author Matthew McNaney <mcnaney at gmail dot com>
-- @version $Id$

CREATE TABLE documents (
  id int NOT NULL default 0,
  file_name varchar(50) NOT NULL,
  file_directory varchar(255) NOT NULL,
  folder_id int not null default 0,
  file_type varchar(30) NOT NULL,
  title varchar(255) NOT NULL,
  description text NULL,
  size int NOT NULL default 0,
  downloaded int NOT NULL default 0,
  PRIMARY KEY  (id)
);

CREATE TABLE images (
  id int NOT NULL default 0,
  file_name varchar(50) NOT NULL,
  file_directory varchar(255) NOT NULL,
  folder_id int not null default 0,
  file_type varchar(30) NOT NULL,
  title varchar(255) NOT NULL,
  description text NULL,
  size int NOT NULL default 0,
  width smallint NOT NULL default 0,
  height smallint NOT NULL default 0,
  alt varchar(255) NOT NULL,
  PRIMARY KEY  (id)
);

CREATE TABLE folders (
  id int not null default 0,
  title varchar(60) not null,
  description text null,
  ftype smallint not null default 1,
  public_folder smallint not null default 1,
  icon varchar(255) not null,
  primary key (id)
);
