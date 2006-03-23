CREATE TABLE layout_styles (
key_id INT NOT NULL ,
style VARCHAR( 40 ) NOT NULL
);

CREATE INDEX key_id ON layout_styles(key_id);
