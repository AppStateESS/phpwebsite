CREATE TABLE rss_channel (
id INT NOT NULL default '0',
module varchar(40) NOT NULL,
title varchar(100) NOT NULL,
description text NULL,
pub_date int NOT NULL default '0',
last_build_date int NOT NULL default '0',
ttl smallint NOT NULL default '0',
image_id int NOT NULL default '0',
active smallint NOT NULL default '1',
PRIMARY KEY ( id )
);

