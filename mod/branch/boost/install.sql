-- @author Matthew McNaney <mcnaney at gmail dot com>
-- @version $Id$

CREATE TABLE branch_sites (
id INT NOT NULL,
branch_name VARCHAR( 50 ) NOT NULL,
directory VARCHAR( 100 ) NOT NULL,
url VARCHAR( 100 ) NOT NULL,
site_hash VARCHAR( 50 ) NOT NULL,
PRIMARY KEY (id)
);

CREATE TABLE branch_mod_limit (
  branch_id int NOT NULL default 0,
  module_name varchar(40) NOT NULL
);
