CREATE TABLE whodis (
  id int NOT NULL default 0,
  created int NOT NULL default 0,
  updated int NOT NULL default 0,
  url varchar(255) NOT NULL,
  visits int NOT NULL default 0,
  PRIMARY KEY  (id)
);

CREATE TABLE whodis_filters (
  id int NOT NULL default 0,
  filter varchar(255) NOT NULL default '',
  PRIMARY KEY  (id)
);
