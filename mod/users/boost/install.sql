-- @author Matthew McNaney <mcnaney at gmail dot com>
-- @version $Id$

CREATE TABLE users_groups (
 id INT NOT NULL PRIMARY KEY,
 active SMALLINT NOT NULL,
 name CHAR(50) NOT NULL,
 user_id INT NOT NULL
 );

CREATE TABLE users_members (
 group_id INT NOT NULL,
 member_id INT NOT NULL
 );

CREATE TABLE users (
  id int NOT NULL default 0,
  last_logged int default 0,
  log_count int NOT NULL default 0,
  authorize smallint NOT NULL default 0,
  created int NOT NULL default 0,
  updated int NOT NULL default 0,
  active smallint NOT NULL default 0,
  approved smallint NOT NULL default 0,
  username varchar(30) NOT NULL,
  display_name varchar(30) NOT NULL,
  email varchar(100) default NULL,
  deity smallint NOT NULL default 0,
  PRIMARY KEY  (id)
);

CREATE TABLE user_authorization (
  username varchar(30) NOT NULL,
  password CHAR(32) NOT NULL,
  PRIMARY KEY (username)
);


CREATE TABLE users_auth_scripts (
  id smallint NOT NULL default 0,
  display_name varchar(40) NOT NULL,
  filename varchar(40) NOT NULL,
  PRIMARY KEY (id)
);

CREATE TABLE users_my_page_mods (
  mod_title varchar(40) NOT NULL
);

CREATE TABLE users_signup (
  authkey char(32) NOT NULL,
  user_id int NOT NULL default 0,
  deadline int NOT NULL default 0
);

CREATE INDEX userssignup_idx on users_signup (authkey);

CREATE TABLE users_pw_reset (
user_id INT NOT NULL default 0,
authhash CHAR( 32 ) NOT NULL default 0,
timeout INT NOT NULL default 0
);
