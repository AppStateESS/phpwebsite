
CREATE TABLE controlpanel_link (
 id INT NOT NULL PRIMARY KEY,
 tab CHAR(255) NOT NULL,
 active SMALLINT NOT NULL,
 label CHAR(50) NULL,	
 itemname CHAR(50) NOT NULL,
 restricted SMALLINT NOT NULL,
 url TEXT,
 description TEXT,
 image CHAR(255),
 link_order SMALLINT NOT NULL
 );

CREATE TABLE controlpanel_tab (
  id int NOT NULL default '0',
  title CHAR(255) NOT NULL default '',
  label CHAR(255) NOT NULL default '',
  link CHAR(255) NOT NULL default '',
  tab_order smallint NOT NULL default '0',
  color CHAR(50) default NULL,
  itemname CHAR(255) NOT NULL default '',
  style CHAR(50) default NULL,
  PRIMARY KEY  (id)
);
