-- @author Matthew McNaney <mcnaney at gmail dot com>
-- @version $Id$

CREATE TABLE categories (
  id int unsigned NOT NULL default 0,
  title varchar(255) NOT NULL,
  description text,
  parent int unsigned NOT NULL default 0,
  icon int unsigned NOT NULL default 0,
  PRIMARY KEY  (id)
);

CREATE TABLE category_items (
  cat_id int unsigned NOT NULL default 0,
  key_id int unsigned NOT NULL default 0,
  module char(40) NOT NULL
);

CREATE INDEX categoryitems_idx on category_items(cat_id, module);
