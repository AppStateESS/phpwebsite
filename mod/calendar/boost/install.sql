CREATE TABLE calendar_events (
  id int NOT NULL default '0',
  key_id int NOT NULL default '0',
  title varchar(60) NOT NULL default '',
  summary text,
  start_time int NOT NULL default '0',
  end_time int NOT NULL default '0',
  post_start int NOT NULL default '0',
  post_end int NOT NULL default '0',
  public smallint NOT NULL default '0',
  sticky smallint NOT NULL default '0',
  PRIMARY KEY  (id)
);

CREATE TABLE calendar_owner (
  id int NOT NULL default '0',
  group_id int NOT NULL default '0',
  title varchar(60) NOT NULL default '',
  email varchar(255) NOT NULL default '',
  public smallint NOT NULL default '0',
  PRIMARY KEY  (id)
);
