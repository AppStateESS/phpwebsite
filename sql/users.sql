CREATE TABLE users_new (
    id int NOT NULL AUTO_INCREMENT,
    username varchar(255) NOT NULL,
    email varchar(255) NOT NULL,
    full_name varchar(255) NOT NULL,
    is_deity int NOT NULL default 0,
    authentication_method_name varchar(255) NOT NULL,
    authorization_method_name varchar(255) NOT NULL,
    last_login_time integer,
    login_count integer NOT NULL,
    created_on_time integer NOT NULL,
    last_modified_time integer NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE users_local_authentication (
    user_id int NOT NULL,
    password_hash varchar(255),
    PRIMARY KEY (user_id)
);
