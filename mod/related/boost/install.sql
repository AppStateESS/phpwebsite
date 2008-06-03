-- @author Matthew McNaney <mcnaney at gmail dot com>
-- @version $Id$

CREATE TABLE related_friends (
  source_id int unsigned NOT NULL default 0,
  friend_id int unsigned NOT NULL default 0,
  rating smallint unsigned NOT NULL default 0
);

CREATE TABLE related_main (
  id int unsigned NOT NULL default 0,
  key_id int unsigned NOT NULL default 0,
  title varchar(40) NOT NULL,
  PRIMARY KEY  (id)
);
