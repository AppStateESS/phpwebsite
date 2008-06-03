-- @author Matthew McNaney <mcnaney at gmail dot com>
-- @version $Id$

CREATE TABLE menu_links (
  id int unsigned NOT NULL default 0,
  menu_id int unsigned NOT NULL default 0,
  key_id int unsigned NOT NULL default 0,
  title varchar(50) NOT NULL,
  url varchar(255) NOT NULL,
  parent int unsigned NOT NULL default 0,
  link_order smallint unsigned NOT NULL default 0,
  PRIMARY KEY  (id)
);

CREATE TABLE menus (
  id int unsigned NOT NULL default 0,
  title varchar(30) NOT NULL,
  template varchar(50) NOT NULL,
  restricted smallint NOT NULL default 0,
  pin_all smallint NOT NULL default 0,
  PRIMARY KEY  (id)
);

CREATE TABLE menu_assoc (
  menu_id int unsigned NOT NULL default 0,
  key_id int unsigned NOT NULL default 0
);
