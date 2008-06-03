-- @author Matthew McNaney <mcnaney at gmail dot com>
-- @version $Id$

CREATE TABLE profiles (
  id int unsigned NOT NULL default 0,
  firstname varchar(40) NOT NULL,
  lastname varchar(40) NOT NULL,
  photo_large int unsigned NOT NULL default 0,
  photo_medium int unsigned NOT NULL default 0,
  photo_small int unsigned NOT NULL default 0,
  website varchar(255) NULL,
  email varchar(60) NULL,
  fullstory text,
  caption text,
  profile_type smallint unsigned NOT NULL default 0,
  keywords text,
  submit_date int unsigned NOT NULL default 0,
  contributor varchar(40) NOT NULL,
  contributor_id int unsigned NOT NULL default 0,
  approved smallint NOT NULL default 0,
  PRIMARY KEY  (id)
);


CREATE TABLE profiler_division (
  id int unsigned NOT NULL default 0,
  title varchar(60) NOT NULL,
  show_homepage smallint NOT NULL default 0,
  PRIMARY KEY  (id)
);
