-- @author Matthew McNaney <mcnaney at gmail dot com>
-- @version $Id$

CREATE TABLE search (
  key_id int NOT NULL default 0,
  module char(40) NOT NULL,
  created int NOT NULL default 0,
  keywords text NOT NULL
);

CREATE INDEX search_index on search (key_id, module, created);

CREATE TABLE search_stats (
  keyword varchar(50) NOT NULL,
  query_success int NOT NULL default 0,
  query_failure int NOT NULL default 0,
  mixed_query int NOT NULL default 0,
  total_query int NOT NULL default 0,
  highest_result smallint NOT NULL default 0,
  last_called int NOT NULL default 0,
  multiple_word int NOT NULL default 0,
  exact_success int NOT NULL default 0,
  ignored smallint NOT NULL default 0
);

CREATE INDEX search_stats_index on search_stats (keyword);
