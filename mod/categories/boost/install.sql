CREATE TABLE categories (
  id int NOT NULL default '0',
  title varchar(255) NOT NULL default '',
  description text,
  parent int NOT NULL default '0',
  image varchar(255) default NULL,
  thumbnail varchar(255) default NULL,
  PRIMARY KEY  (id)
);


CREATE TABLE categories_children (
  cat_id int NOT NULL default '0',
  kid_id int NOT NULL default '0'
);

CREATE TABLE categories_modules (
  mod_title varchar(40) NOT NULL default ''
);
