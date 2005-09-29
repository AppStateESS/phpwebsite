CREATE TABLE access_shortcuts (
  id int(11) NOT NULL default '0',
  keyword varchar(40) NOT NULL default '',
  url varchar(255) NOT NULL default '',
  active smallint(6) NOT NULL default '0',
  PRIMARY KEY  (id)
);

CREATE TABLE access_allow_deny (
  id int(11) NOT NULL default '0',
  ip_address varchar(40) NOT NULL default '',
  allow_or_deny smallint(6) NOT NULL default '0',
  active smallint(6) NOT NULL default '0',
  PRIMARY KEY  (id)
);
