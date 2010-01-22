-- @author Jeff Tickle <jtickle at tux dot appstate dot edu>
-- @version $Id: install.sql 6503 2008-11-25 18:08:17Z matt $

CREATE TABLE poll (
    id int NOT NULL,
    key_id int,
    question VARCHAR(255) NOT NULL,
    response1 VARCHAR(255) NOT NULL,
    response2 VARCHAR(255) NOT NULL,
    creator int NOT NULL,
    created int NOT NULL,
    count1 int,
    count2 int,
    last_vote_time int,
    PRIMARY KEY (id),
    INDEX (key_id)
);

CREATE TABLE poll_vote (
    id int NOT NULL,
    poll_id int NOT NULL,
    ip unsigned int NOT NULL default 0,
    user_id int,
    value int NOT NULL default 0,
    PRIMARY KEY (id)
);
