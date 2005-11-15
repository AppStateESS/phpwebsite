CREATE TABLE rssfeeds (
  module varchar(40) NOT NULL default '',
  serve_limit smallint NOT NULL default '10',
  age_limit smallint NOT NULL default '0',
  times_accessed int NOT NULL default '0',
  last_cached int NOT NULL default '0',
  cache_timeout int NOT NULL default '0',
  active smallint NOT NULL default '0'
);
