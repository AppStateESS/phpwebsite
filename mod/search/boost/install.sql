CREATE TABLE search (
  key_id int(11) NOT NULL default '0',
  keywords text NOT NULL,
  INDEX key_id (key_id)
);

CREATE TABLE search_stats (
  keyword varchar(50) NOT NULL default '',
  success int(11) NOT NULL default '0',
  fail int(11) NOT NULL default '0',
  total int(11) NOT NULL default '0',
  last_called int(11) NOT NULL default '0',
  INDEX keyword (keyword)
);
