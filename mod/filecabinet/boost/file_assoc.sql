CREATE TABLE fc_file_assoc (
id INT NOT NULL default 0,
file_type SMALLINT NOT NULL default 0,
file_id INT NOT NULL default 0,
resize varchar(30) NULL,
PRIMARY KEY ( id )
);
