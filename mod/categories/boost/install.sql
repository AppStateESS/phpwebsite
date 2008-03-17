-- @author Matthew McNaney <mcnaney at gmail dot com>
-- @version $Id$

CREATE TABLE categories (
  id int NOT NULL default 0,
  title varchar(255) NOT NULL,
  description text,
  parent int NOT NULL default 0,
  icon int default NOT NULL default 0,
  PRIMARY KEY  (id)
);

CREATE TABLE category_items (
  cat_id int NOT NULL default 0,
  key_id int NOT NULL default 0,
  module char(40) NOT NULL
);

CREATE INDEX categoryitems_idx on category_items(cat_id, module);
