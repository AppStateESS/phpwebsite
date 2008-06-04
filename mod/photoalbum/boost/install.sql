-- @author Matthew McNaney <mcnaney at gmail dot com>
-- @version $Id: install.sql 27 2006-11-15 16:26:12Z matt $

CREATE TABLE mod_photoalbum_albums (
  id int NOT NULL default 0,
  key_id int NOT NULL default 0,
  owner varchar(20) binary,
  editor varchar(20) binary,
  ip text,
  label text NOT NULL,
  groups mediumtext,
  created int NOT NULL default 0,
  updated int NOT NULL default 0,
  hidden smallint NOT NULL default 1,
  approved smallint NOT NULL default 0,
  blurb0 text,
  blurb1 text,
  image text default NULL,
  PRIMARY KEY  (id)
);

CREATE TABLE mod_photoalbum_photos (
  id int NOT NULL default 0,
  owner varchar(20) binary,
  editor varchar(20) binary,
  ip text default NULL,
  label text NOT NULL,
  groups mediumtext,
  created int NOT NULL default 0,
  updated int NOT NULL default 0,
  hidden smallint NOT NULL default 1,
  approved smallint NOT NULL default 0,
  album int NOT NULL default 0,
  name varchar(255) default NULL,
  type varchar(60) default NULL,
  width smallint default NULL,
  height smallint default NULL,
  tnname varchar(255) default NULL,
  tnwidth smallint default NULL,
  tnheight smallint default NULL,
  blurb text,
  PRIMARY KEY  (id)
);
