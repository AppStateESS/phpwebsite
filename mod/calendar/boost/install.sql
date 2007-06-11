-- @author Matthew McNaney <mcnaney at gmail dot com>
-- @version $Id$

CREATE TABLE calendar_schedule (
  id int NOT NULL default 0,
  key_id int NOT NULL default 0,
  user_id int NOT NULL default 0,
  title varchar(60) NOT NULL,
  show_upcoming smallint NOT NULL default 0,
  summary text,
  public smallint NOT NULL default 0,
  PRIMARY KEY (id)
);

CREATE TABLE calendar_notice (
  id int NOT NULL default 0,
  user_id int NOT NULL default 0,
  email varchar(255) NOT NULL,
  PRIMARY KEY  (id)
);

CREATE TABLE calendar_suggestions (
  id int NOT NULL default 0,
  schedule_id int NOT NULL default 0,
  summary varchar(60) NOT NULL,
  location varchar(60) default NULL,
  loc_link varchar(255) default NULL,
  description text,
  all_day smallint NOT NULL default 0,
  start_time int NOT NULL default 0,
  end_time int NOT NULL default 0,
  submitted int NOT NULL default 0,
  PRIMARY KEY  (id)
);
