CREATE TABLE categories (
  id int NOT NULL default '0',
  title varchar(255) NOT NULL default '',
  description text,
  parent int NOT NULL default '0',
  image varchar(255) default NULL,
  thumbnail varchar(255) default NULL,
  PRIMARY KEY  (id)
);

CREATE TABLE category_items (
  cat_id int(11) NOT NULL default '0',
  item_id int(11) NOT NULL default '0',
  module varchar(40) NOT NULL default '',
  item_name varchar(40) NOT NULL default '',
  label varchar(255) NOT NULL default '',
  link varchar(255) NOT NULL default ''
);
