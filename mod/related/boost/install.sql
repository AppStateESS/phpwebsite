CREATE TABLE related_friends (
  source_id int NOT NULL default '0',
  friend_id int NOT NULL default '0',
  rating smallint NOT NULL default '0'
);

CREATE TABLE related_main (
  id int NOT NULL default '0',
  main_id int NOT NULL default '0',
  module varchar(40) NOT NULL default '',
  item_name varchar(40) NOT NULL default '',
  title varchar(255) NOT NULL default '',
  url varchar(255) NOT NULL default '',
  active smallint NOT NULL default '0',
  PRIMARY KEY  (id)
);
