-- @author Matthew McNaney <mcnaney at gmail dot com>
-- @version $Id$

CREATE TABLE webpage_volume (
  id int unsigned NOT NULL default 0,
  key_id int unsigned NOT NULL default 0,
  title varchar(255) NOT NULL,
  summary text,
  date_created int unsigned NOT NULL default 0,
  date_updated int unsigned NOT NULL default 0,
  create_user_id int unsigned NOT NULL default 0,
  created_user varchar(40) NOT NULL,
  update_user_id int unsigned NOT NULL default 0,
  updated_user varchar(40) NOT NULL,
  frontpage smallint NOT NULL default 0,
  approved smallint NOT NULL default 0,
  active smallint NOT NULL default 0,
  PRIMARY KEY  (id)
);

CREATE INDEX webpagevolume_idx on webpage_volume(key_id);

CREATE TABLE webpage_page (
  id int unsigned NOT NULL default 0,
  volume_id int unsigned NOT NULL default 0,
  title varchar(255) NULL,
  content text NOT NULL,
  page_number smallint unsigned NOT NULL default 0,
  template varchar(40) NOT NULL,
  approved smallint NOT NULL default 0,
  image_id int unsigned NOT NULL default 0,
  PRIMARY KEY  (id)
);

CREATE INDEX webpagepage_idx on webpage_page(volume_id);

CREATE TABLE webpage_featured (
  id int unsigned NOT NULL default 0,
  vol_order int unsigned NOT NULL default 0
);
