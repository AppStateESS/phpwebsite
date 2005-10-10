CREATE TABLE categories (
  id int NOT NULL default '0',
  title varchar(255) NOT NULL default '',
  description text,
  parent int NOT NULL default '0',
  icon varchar(255) default NULL,
  PRIMARY KEY  (id)
);

CREATE TABLE category_items (
  cat_id int NOT NULL default '0',
  key_id int NOT NULL default '0'
);
