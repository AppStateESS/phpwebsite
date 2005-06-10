CREATE TABLE layout_box (
id INT NOT NULL PRIMARY KEY,
theme CHAR(40) NOT NULL,
content_var CHAR(40),
module CHAR(40),
theme_var CHAR(40),
box_order SMALLINT NOT NULL, 
active SMALLINT NOT NULL);

CREATE TABLE layout_config (
  default_theme varchar(50) NOT NULL default '',
  userAllow smallint NOT NULL default '0',
  page_title varchar(255) default NULL,
  meta_keywords text,
  meta_description varchar(180) default NULL,
  meta_robots char(2) default NULL,
  meta_owner varchar(40) default NULL,
  meta_author varchar(40) default NULL,
  meta_content varchar(40) NOT NULL default '',
  header text default NULL,
  footer text default NULL,
  cache smallint NOT NULL default '0'
);

INSERT INTO layout_config VALUES ('default', 1, 'phpWebSite', 'phpwebsite', NULL, '11', NULL, NULL, 'ISO-8859-1', NULL, NULL, 1);
