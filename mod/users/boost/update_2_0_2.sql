ALTER TABLE users_config ADD user_menu VARCHAR( 40 ) NOT NULL;
ALTER TABLE users_config ADD graphic_confirm SMALLINT NOT NULL DEFAULT 0;
ALTER TABLE users_config DROP COLUMN default_group;
UPDATE users_config set user_menu='Default.tpl';
