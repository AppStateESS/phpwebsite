CREATE TABLE alert_participant (
id INT NOT NULL ,
email VARCHAR( 50 ) NOT NULL ,
PRIMARY KEY ( id )
);

CREATE TABLE alert_prt_to_type (
prt_id INT NOT NULL ,
type_id INT NOT NULL ,
INDEX ( prt_id , type_id )
);


CREATE TABLE alert_contact (
prt_id INT NOT NULL ,
alert_id INT NOT NULL
) ENGINE = MYISAM ;

CREATE TABLE alert_item (
id INT NOT NULL ,
title VARCHAR( 255 ) NOT NULL ,
description TEXT NOT NULL ,
create_date INT NOT NULL ,
update_date INT NOT NULL ,
created_by_id INT NOT NULL ,
created_name varchar(50) not null,
updated_by_id INT NOT NULL ,
updated_name varchar(50) not null,
type_id INT NOT NULL ,
contact_complete INT NOT NULL ,
active SMALLINT NOT NULL,
PRIMARY KEY ( id )
);

CREATE TABLE alert_type (
id INT NOT NULL ,
title VARCHAR( 255 ) NOT NULL ,
email SMALLINT NOT NULL ,
rssfeed SMALLINT NOT NULL ,
post_type SMALLINT NOT NULL ,
default_alert TEXT NULL ,
PRIMARY KEY ( id )
);
