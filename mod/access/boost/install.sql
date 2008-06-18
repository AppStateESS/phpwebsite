-- @author Matthew McNaney <mcnaney at gmail dot com>
-- @version $Id$

CREATE TABLE access_shortcuts (
  id int NOT NULL default 0,
  keyword varchar(40) NOT NULL,
  url varchar(255) NOT NULL,
  active smallint NOT NULL default 0,
  PRIMARY KEY  (id)
);

CREATE TABLE access_allow_deny (
  id int NOT NULL default 0,
  ip_address varchar(40) NOT NULL,
  allow_or_deny smallint NOT NULL default 0,
  active smallint NOT NULL default 0,
  PRIMARY KEY  (id)
);
