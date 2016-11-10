CREATE TABLE users_new (
    id int NOT NULL,
    username varchar(255) NOT NULL,
    email varchar(255) NOT NULL,
    fullName varchar(255) NOT NULL,
    isDeity int NOT NULL default 0,
    authenticationMethodName varchar(255) NOT NULL,
    authorizationMethodName varchar(255) NOT NULL,
    lastLoginTime integer,
    loginCount integer NOT NULL,
    createdOnTime integer NOT NULL,
    lastModifiedTime integer NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE users_passwords (
    user_id int NOT NULL,
    password_hash varchar(255),
    PRIMARY KEY (user_id)
);
