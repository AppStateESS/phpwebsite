CREATE TABLE {TABLE} (
  id int NOT NULL default '0',
  title varchar(60) NOT NULL default '',
  summary text,
  event_type smallint NOT NULL default '0',
  start_time int NOT NULL default '0',
  end_time int NOT NULL default '0',
  PRIMARY KEY  (id)
);
