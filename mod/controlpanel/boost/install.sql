-- @author Matthew McNaney <mcnaney at gmail dot com>
-- @version $Id$

CREATE TABLE controlpanel_tab (
  id CHAR(255) NOT NULL,
  title CHAR(255) NOT NULL,
  link CHAR(255) NOT NULL,
  tab_order smallint NOT NULL default 0,
  itemname CHAR(255) NOT NULL
);

CREATE TABLE controlpanel_link (
 id INT NOT NULL PRIMARY KEY,
 tab CHAR(255) NOT NULL,
 active SMALLINT NOT NULL,
 label CHAR(50) NULL,	
 itemname CHAR(50) NOT NULL,
 restricted SMALLINT NOT NULL default 0,
 url TEXT,
 description TEXT,
 image CHAR(255),
 link_order SMALLINT NOT NULL
 );
