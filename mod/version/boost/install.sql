CREATE TABLE version_approval (
version_id INT NOT NULL ,
module VARCHAR( 40 ) NOT NULL ,
vr_table VARCHAR( 50 ) NOT NULL ,
info VARCHAR( 255 ) NOT NULL ,
view_url VARCHAR( 255 ) NOT NULL ,
edit_url VARCHAR( 255 ) NOT NULL ,
approve_url VARCHAR( 255 ) NOT NULL ,
refuse_url VARCHAR( 255 ) NOT NULL ,
INDEX ( version_id )
);
