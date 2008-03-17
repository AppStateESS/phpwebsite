CREATE TABLE categories (
  id int NOT NULL default 0,
  title varchar(255) NOT NULL,
  description text,
  parent int NOT NULL default 0,
  icon int NOT NULL default 0,
  PRIMARY KEY  (id)
);
