CREATE TABLE dependencies (
  source_mod varchar(40) NOT NULL default '',
  depended_on varchar(40) NOT NULL default '',
  version varchar(20) NOT NULL default ''
);
