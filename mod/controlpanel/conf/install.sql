CREATE TABLE controlpanel_link ( id INT NOT NULL PRIMARY KEY, label CHAR(255) NOT NULL, active SMALLINT NOT NULL, itemname CHAR(50) NOT NULL, restricted SMALLINT NOT NULL, tab INT NOT NULL, url TEXT, description TEXT, image INT NOT NULL, link_order SMALLINT NOT NULL );

CREATE TABLE controlpanel_tab ( id INT NOT NULL PRIMARY KEY, title CHAR(255) NOT NULL, link CHAR(255) NOT NULL, tab_order SMALLINT NOT NULL, tabfile CHAR(255) , color CHAR(50) , itemname CHAR(255) NOT NULL, style CHAR(50) );
