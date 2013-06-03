CREATE TABLE modules (
 title CHAR(40) NOT NULL,
 proper_name CHAR(40) NOT NULL,
 priority SMALLINT NOT NULL,
 active SMALLINT NOT NULL,
 version CHAR(20) NOT NULL,
 register SMALLINT NOT NULL,
 unregister SMALLINT NOT NULL,
 deprecated smallint not null default 1,
 PRIMARY KEY (title)
);

CREATE TABLE settings (
  module_name varchar(255) NOT NULL,
  variable_name varchar(255) NOT NULL,
  setting text NOT NULL
);


CREATE TABLE registered (
 module CHAR(40) NOT NULL,
 registered_to CHAR(40) NOT NULL
);


CREATE TABLE dependencies (
  source_mod varchar(40) NOT NULL,
  depended_on varchar(40) NOT NULL,
  version varchar(20) NOT NULL
);

CREATE TABLE mod_settings (
 module varchar(40) NOT NULL,
 setting_name varchar(60) NOT NULL,
 setting_type smallint NOT NULL default 4,
 small_num smallint NOT NULL default 0,
 large_num int NOT NULL default 0,
 small_char varchar(100) NULL,
 large_char text NULL
);

CREATE INDEX modsettings_idx ON mod_settings (module, setting_name);

CREATE TABLE phpws_key (
  id int NOT NULL default 0,
  module varchar(40) NOT NULL,
  item_name varchar(40) NOT NULL,
  item_id int NOT NULL default 0,
  title varchar(255) default NULL,
  summary varchar(255) default NULL,
  url varchar(255) default NULL,
  active smallint NOT NULL default 1,
  restricted smallint NOT NULL default 0,
  create_date int NOT NULL default 0,
  update_date int NOT NULL default 0,
  creator varchar(60) default NULL,
  creator_id int NOT NULL default 0,
  updater varchar(60) default NULL,
  updater_id int NOT NULL default 0,
  times_viewed int NOT NULL default 0,
  edit_permission varchar(30) default NULL,
  show_after int NOT NULL default 0,
  hide_after int NOT NULL default 2147400000,
  PRIMARY KEY  (id)
);

CREATE INDEX phpwskey_idx on phpws_key (restricted, active, module, create_date, update_date);

CREATE TABLE phpws_key_view (
  key_id int NOT NULL default 0,
  group_id int NOT NULL default 0
);

CREATE INDEX phpwskeyview_idx on phpws_key_view (key_id);

CREATE TABLE phpws_key_edit (
  key_id int NOT NULL default 0,
  group_id int NOT NULL default 0
);

CREATE INDEX phpwskeyedit_idx on phpws_key_edit (key_id);


CREATE TABLE phpws_key_register (
  module varchar(40) NOT NULL
);

CREATE TABLE converted (
  convert_name varchar(30) NOT NULL
);

CREATE TABLE core_version (
  version varchar(10) NOT NULL
);
