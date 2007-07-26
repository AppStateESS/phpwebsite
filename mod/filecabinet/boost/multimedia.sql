CREATE TABLE multimedia (
  id int NOT NULL default 0,
  file_name varchar(50) NOT NULL,
  file_directory varchar(255) NOT NULL,
  folder_id int NOT NULL default 0,
  file_type varchar(30) NOT NULL,
  title varchar(255) NOT NULL,
  description text,
  size int NOT NULL default 0,
  width smallint NOT NULL default 0,
  height smallint NOT NULL default 0,
  PRIMARY KEY  (id)
);
