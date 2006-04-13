CREATE TABLE branch_sites (
id INT NOT NULL,
branch_name VARCHAR( 50 ) NOT NULL,
directory VARCHAR( 100 ) NOT NULL,
url VARCHAR( 100 ) NOT NULL,
hash VARCHAR( 50 ) NOT NULL,
PRIMARY KEY (id)
);
