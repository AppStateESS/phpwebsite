CREATE TABLE layout_box (
id INT NOT NULL PRIMARY KEY,
theme CHAR(40) NOT NULL,
content_var CHAR(40),
module CHAR(40),
theme_var CHAR(40),
template CHAR(40),
box_order SMALLINT NOT NULL, 
active SMALLINT NOT NULL);

CREATE TABLE layout_config (
default_theme CHAR(50) NOT NULL,
userAllow SMALLINT NOT NULL,
page_title CHAR(255),
meta_keywords TEXT,
meta_description CHAR(180),
meta_robots CHAR(2),
meta_owner CHAR(40),
meta_author CHAR(40),
meta_content CHAR(40) NOT NULL
);

INSERT INTO layout_config VALUES ('default', 1, 'phpWebSite', 'phpwebsite', NULL, '11', NULL, NULL, 'ISO-8859-1');
