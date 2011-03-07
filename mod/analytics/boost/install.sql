CREATE TABLE analytics_tracker (
    id integer NOT NULL PRIMARY KEY,
    name varchar(255) NOT NULL,
    type varchar(255) NOT NULL,
    active smallint NOT NULL DEFAULT 0,
    account varchar(255)
);
