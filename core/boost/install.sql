CREATE TABLE modules ( 
	title CHAR(40) NOT NULL, 
	proper_name CHAR(40) NOT NULL,
	priority SMALLINT NOT NULL, 
	active SMALLINT NOT NULL, 
	version CHAR(20) NOT NULL, 
	register SMALLINT NOT NULL,
	pre94 SMALLINT NOT NULL
	);

CREATE TABLE registered ( 
	module CHAR(40) NOT NULL, 
	registered CHAR(40) NOT NULL
	);
