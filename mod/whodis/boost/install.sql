CREATE TABLE whodis (
  id int unsigned NOT NULL default 0,
  created int unsigned NOT NULL default 0,
  updated int unsigned NOT NULL default 0,
  url varchar(255) NOT NULL,
  visits int unsigned NOT NULL default 0,
  PRIMARY KEY  (id)
);

CREATE TABLE whodis_filters (
  id int unsigned NOT NULL default 0,
  filter varchar(255) NOT NULL default '',
  PRIMARY KEY  (id)
);
