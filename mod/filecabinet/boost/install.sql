-- @author Matthew McNaney <mcnaney at gmail dot com>
-- @version $Id$

CREATE TABLE documents (
  id int NOT NULL default 0,
  key_id int NOT NULL default 0,
  file_name varchar(50) NOT NULL,
  file_directory varchar(150) NOT NULL,
  file_type varchar(30) NOT NULL,
  title varchar(255) NOT NULL,
  description text NULL,
  size int NOT NULL default 0,
  downloaded int NOT NULL default 0,
  PRIMARY KEY  (id)
);

CREATE INDEX documents_idx on documents(key_id);

CREATE TABLE images (
  id int NOT NULL default 0,
  key_id int NOT NULL default 0,
  file_name varchar(50) NOT NULL,
  file_directory varchar(150) NOT NULL,
  file_type varchar(30) NOT NULL,
  title varchar(255) NOT NULL,
  description text NULL,
  size int NOT NULL default 0,
  width smallint NOT NULL default 0,
  height smallint NOT NULL default 0,
  alt varchar(255) NOT NULL,
  thumbnail_source int NOT NULL default 0,
  PRIMARY KEY  (id)
);

CREATE INDEX images_idx on images(key_id);
