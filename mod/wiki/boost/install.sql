-- $Id: install.sql,v 1.21 2006/08/15 03:51:27 blindman1344 Exp $

CREATE TABLE wiki_pages (
  id int NOT NULL default '0',
  key_id int NOT NULL default '0',
  owner_id int NOT NULL default '0',
  editor_id int NOT NULL default '0',
  title varchar(255) NOT NULL,
  created int NOT NULL default '0',
  updated int NOT NULL default '0',
  pagetext text NOT NULL,
  hits int NOT NULL default '0',
  comment text NOT NULL,
  allow_edit smallint NOT NULL default 1,
  PRIMARY KEY (id)
);

CREATE TABLE wiki_images (
  id int NOT NULL default '0',
  owner_id int NOT NULL default '0',
  created int NOT NULL default '0',
  filename text NOT NULL,
  size int NOT NULL default '0',
  type varchar(255) NOT NULL,
  summary text NOT NULL,
  PRIMARY KEY  (id)
);

CREATE TABLE wiki_interwiki (
  id int NOT NULL default '0',
  owner_id int NOT NULL default '0',
  editor_id int NOT NULL default '0',
  label text NOT NULL,
  created int NOT NULL default '0',
  updated int NOT NULL default '0',
  url text NOT NULL,
  PRIMARY KEY  (id)
);