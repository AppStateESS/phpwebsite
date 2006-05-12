CREATE TABLE users_signup (
  authkey char(32) NOT NULL default '',
  user_id int NOT NULL default '0',
  deadline int NOT NULL default '0'
);

CREATE INDEX userssignup_idx on user_authorization (username, password);
