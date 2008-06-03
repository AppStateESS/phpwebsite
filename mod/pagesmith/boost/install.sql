CREATE TABLE ps_block (
  id int unsigned NOT NULL default 0,
  pid int unsigned NOT NULL default 0,
  btype varchar(10) NOT NULL,
  type_id int unsigned NOT NULL default 0,
  secname varchar(30) NOT NULL,
  sectype varchar(20) NOT NULL,
  width smallint unsigned default 0,
  height smallint unsigned default 0,
  PRIMARY KEY  (id)
);

CREATE INDEX psblock_idx on ps_block(pid);

CREATE TABLE ps_page (
  id int unsigned NOT NULL default 0,
  key_id int unsigned NOT NULL default 0,
  title varchar(255) NOT NULL,
  template varchar(20) NOT NULL,
  create_date int unsigned not null default 0,
  last_updated int unsigned not null default 0,
  front_page smallint NOT NULL default 0,
  PRIMARY KEY  (id)
);

CREATE INDEX pspage_idx on ps_page(key_id);


CREATE TABLE ps_text (
  id int unsigned NOT NULL default 0,
  pid int unsigned NOT NULL default 0,
  content text NOT NULL,
  secname varchar(30) NOT NULL,
  sectype varchar(20) NOT NULL,
  PRIMARY KEY  (id)
);

CREATE INDEX pstext_idx on ps_text(pid);
