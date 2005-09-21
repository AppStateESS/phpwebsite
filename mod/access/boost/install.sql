CREATE TABLE access_shortcuts (
  keyword varchar(40) NOT NULL default '',
  url varchar(255) NOT NULL default '',
  KEY keyword (keyword)
);
