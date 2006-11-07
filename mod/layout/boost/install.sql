-- @author Matthew McNaney <mcnaney at gmail dot com>
-- @version $Id$

CREATE TABLE layout_box (
id INT NOT NULL PRIMARY KEY,
theme varchar(40) NOT NULL,
content_var varchar(40),
module varchar(40),
theme_var varchar(40),
box_order SMALLINT NOT NULL, 
active SMALLINT NOT NULL);

CREATE TABLE layout_config (
  default_theme varchar(50) NOT NULL,
  userAllow smallint NOT NULL default 0,
  page_title varchar(255) default NULL,
  meta_keywords text,
  meta_description varchar(180) default NULL,
  meta_robots char(2) default NULL,
  meta_owner varchar(40) default NULL,
  meta_author varchar(40) default NULL,
  meta_content varchar(40) NOT NULL,
  header text default NULL,
  footer text default NULL,
  cache smallint NOT NULL default 0
);

INSERT INTO layout_config VALUES ('default', 1, 'phpWebSite', 'phpwebsite', NULL, '11', NULL, NULL, 'utf-8', NULL, NULL, 1);

CREATE TABLE layout_styles (
key_id INT NOT NULL ,
style varchar( 40 ) NOT NULL
);

CREATE INDEX key_id ON layout_styles(key_id);
