-- @author Jeff Tickle <jtickle at tux dot appstate dot edu>
-- @version $Id: install.sql 6503 2008-11-25 18:08:17Z matt $

CREATE TABLE link (
    id INT NOT NULL,
    key_id INT NOT NULL,
    href TEXT NOT NULL,
    title TEXT NOT NULL,
    other TEXT NOT NULL,
    placement INT NOT NULL,
    rating FLOAT NOT NULL DEFAULT 2.5,
    PRIMARY KEY(id),
    KEY(key_id)
);

CREATE TABLE link_activity (
    id INT NOT NULL,
    link_id INT NOT NULL,
    ip INT NOT NULL,
    user_id INT NOT NULL DEFAULT 0,
    action INT NOT NULL,
    PRIMARY KEY(id),
    KEY(link_id)
);

CREATE TABLE link_rating (
    id INT NOT NULL,
    link_id INT NOT NULL,
    ip INT NOT NULL,
    rating INT NOT NULL,
    PRIMARY KEY(id),
    KEY(link_id)
);
