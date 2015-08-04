CREATE TABLE ps_block (
  id int NOT NULL default 0,
  pid int NOT NULL default 0,
  type_id int NOT NULL default 0,
  secname varchar(30) NOT NULL,
  sectype varchar(20) NOT NULL,
  width smallint default 0,
  height smallint default 0,
  PRIMARY KEY  (id)
);

CREATE INDEX psblock_idx on ps_block(pid);

CREATE TABLE ps_page (
  id int NOT NULL default 0,
  key_id int NOT NULL default 0,
  title varchar(255) NOT NULL,
  template varchar(20) NOT NULL,
  create_date int not null default 0,
  last_updated int not null default 0,
  front_page smallint NOT NULL default 0,
  parent_page int NOT NULL default 0,
  page_order smallint NOT NULL default 0,
  hide_title smallint NOT NULL default 0,
  deleted smallint NOT NULL default 0,
  PRIMARY KEY  (id)
);

CREATE INDEX pspage_idx on ps_page(key_id);


CREATE TABLE ps_text (
  id int NOT NULL default 0,
  pid int NOT NULL default 0,
  content longtext,
  secname varchar(30) NOT NULL,
  sectype varchar(20) NOT NULL,
  PRIMARY KEY  (id)
);

CREATE INDEX pstext_idx on ps_text(pid);
