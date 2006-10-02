CREATE TABLE documents (
  id int NOT NULL default '0',
  key_id int NOT NULL default '0',
  file_name varchar(50) NOT NULL default '',
  file_directory varchar(150) NOT NULL default '',
  file_type varchar(30) NOT NULL default '',
  title varchar(255) NOT NULL default '',
  description text NULL,
  size int NOT NULL default '0',
  downloaded int NOT NULL default '0',
  PRIMARY KEY  (id)
);

CREATE INDEX documents_idx on documents(key_id);

CREATE TABLE images (
  id int NOT NULL default '0',
  key_id int NOT NULL default '0',
  file_name varchar(50) NOT NULL default '',
  file_directory varchar(150) NOT NULL default '',
  file_type varchar(30) NOT NULL default '',
  title varchar(255) NOT NULL default '',
  description text NULL,
  size int NOT NULL default '0',
  width smallint NOT NULL default '0',
  height smallint NOT NULL default '0',
  alt varchar(255) NOT NULL default '',
  thumbnail_source int NOT NULL default '0',
  PRIMARY KEY  (id)
);

CREATE INDEX images_idx on documents(key_id);
