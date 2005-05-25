CREATE TABLE menu_links (
  id int NOT NULL default '0',
  module varchar(30) NOT NULL default '',
  item_name varchar(30) NOT NULL default '',
  item_id int NOT NULL default '0',
  url varchar(255) default NULL,
  parent int NOT NULL default '0',
  active smallint NOT NULL default '0',
  link_order smallint NOT NULL default '0',
  PRIMARY KEY  (id)
);

CREATE TABLE menus (
  id int NOT NULL default '0',
  title varchar(30) NOT NULL default '',
  menu_order smallint NOT NULL default '1',
  PRIMARY KEY  (id)
);
