-- vlist - phpwebsite module
-- @version $Id:  $
-- @author Verdon Vaillancourt <verdonv at gmail dot com>

ALTER TABLE vlist_listing CHANGE active approved smallint not null default 0 ;
ALTER TABLE vlist_listing ADD active smallint not null default 1 AFTER approved ;
