CREATE TABLE IF NOT EXISTS settings (
  module_name varchar(255) NOT NULL,
  variable_name varchar(255) NOT NULL,
  setting text NOT NULL,
  UNIQUE KEY modvar (module_name,variable_name)
);

CREATE TABLE IF NOT EXISTS modules (
  title char(40) NOT NULL,
  proper_name char(40) NOT NULL,
  priority smallint(6) NOT NULL DEFAULT '0',
  active smallint(6) NOT NULL DEFAULT '0',
  version char(20) NOT NULL,
  register smallint(6) NOT NULL DEFAULT '0',
  unregister smallint(6) NOT NULL DEFAULT '0',
  deprecated smallint(6) NOT NULL,
  PRIMARY KEY (title)
);
