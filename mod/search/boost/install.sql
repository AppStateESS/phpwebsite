CREATE TABLE search (
  key_id int(11) NOT NULL default '0',
  keywords text NOT NULL,
  INDEX key_id (key_id)
);

CREATE TABLE search_stats (
  keyword varchar(50) NOT NULL default '',
  query_success int NOT NULL default '0',
  query_failure int NOT NULL default '0',
  mixed_query int NOT NULL default '0',
  total_query int NOT NULL default '0',
  highest_result smallint(6) NOT NULL default '0',
  last_called int NOT NULL default '0',
  multiple_word int NOT NULL default '0',
  exact_success int NOT NULL default '0',
  ignored smallint NOT NULL default '0',
  INDEX keyword (keyword)
);
