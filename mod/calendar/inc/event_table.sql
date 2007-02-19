CREATE TABLE {TABLE} (
  id int NOT NULL default 0,
  key_id int NOT NULL default 0,
  summary varchar(60) NOT NULL default '',
  location varchar(60) default NULL,
  loc_link varchar(255) default NULL,
  description text,
  all_day smallint NOT NULL default 0,
  start_time int NOT NULL default 0,
  end_time int NOT NULL default 0,
  show_busy smallint NOT NULL default 0,
  repeat_type varchar(50) default NULL,
  end_repeat int NOT NULL default 0,
  pid int NOT NULL default 0,
  PRIMARY KEY  (id)
);

CREATE INDEX {INDEX_NAME} on {TABLE} (start_time, end_time);
