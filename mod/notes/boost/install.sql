CREATE TABLE notes (
  id int NOT NULL default '0',
  user_id int NOT NULL default '0',
  check_key varchar(32) NOT NULL default '',
  title varchar(60) NOT NULL default '',
  content text,
  PRIMARY KEY  (id)
);
