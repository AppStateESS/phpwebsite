CREATE TABLE layout_metatags (
key_id INT NOT NULL ,
page_title VARCHAR( 255 ) NOT NULL ,
meta_description TEXT NULL ,
meta_keywords TEXT NULL ,
meta_robots CHAR( 2 ) NOT NULL DEFAULT 11,
PRIMARY KEY ( key_id )
);
