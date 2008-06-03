-- @author Matthew McNaney <mcnaney at gmail dot com>
-- @version $Id$

CREATE TABLE rss_channel (
id INT UNSIGNED NOT NULL default 0,
module varchar(40) NOT NULL,
title varchar(100) NOT NULL,
description text NULL,
pub_date int unsigned NOT NULL default 0,
ttl smallint unsigned NOT NULL default 0,
image_id int unsigned NOT NULL default 0,
active smallint NOT NULL default 1,
PRIMARY KEY ( id )
);


CREATE TABLE rss_feeds (
  id int unsigned NOT NULL default 0,
  title varchar(100) NOT NULL,
  address varchar(255) NOT NULL,
  display smallint NOT NULL default 0,
  refresh_time smallint unsigned NOT NULL default 0,
  item_limit smallint unsigned NOT NULL default 0,
  PRIMARY KEY  (id)
);


