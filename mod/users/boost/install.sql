CREATE TABLE users_config ( anonymous INT NOT NULL );

CREATE TABLE users_demographic_items ( label CHAR(20) NOT NULL, input_type CHAR(15) NOT NULL, presets TEXT );

CREATE TABLE users_groups ( id INT NOT NULL PRIMARY KEY, active SMALLINT NOT NULL, name CHAR(50) NOT NULL, user_id SMALLINT NOT NULL );

CREATE TABLE users_item_permissions ( group_id INT NOT NULL, item_id INT NOT NULL );

CREATE TABLE users_members ( group_id INT NOT NULL, member_id INT NOT NULL );

CREATE TABLE users_permissions ( group_id INT NOT NULL, add_edit_users SMALLINT NOT NULL, delete_users SMALLINT NOT NULL, add_edit_groups SMALLINT NOT NULL, delete_groups SMALLINT NOT NULL, edit_permissions SMALLINT NOT NULL );

CREATE TABLE users_settings ( id INT NOT NULL, label CHAR(30) NOT NULL, var_name CHAR(100) NOT NULL, var_value TEXT );
CREATE TABLE users ( id INT NOT NULL PRIMARY KEY, created INT NOT NULL, updated INT NOT NULL, active SMALLINT NOT NULL, approved SMALLINT NOT NULL, username CHAR(255) NOT NULL, password CHAR(255) NULL, deity SMALLINT NOT NULL );

CREATE TABLE users_demographics (
  label CHAR(20) NOT NULL default '',
  input_type CHAR(20) NOT NULL default '',
  special_info text,
  proper_name CHAR(50) default NULL,
  required smallint NOT NULL default '0',
  active smallint NOT NULL default '0'
);
