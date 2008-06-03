-- @author Matthew McNaney <mcnaney at gmail dot com>
-- @version $Id$

CREATE TABLE demographics (
  user_id int unsigned NOT NULL default 0
);

CREATE UNIQUE INDEX user_id on demographics(user_id);
