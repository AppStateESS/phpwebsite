-- podcaster - phpwebsite module
-- @version $Id: $
-- @author Verdon Vaillancourt <verdonv at gmail dot com>

ALTER TABLE podcaster_channel ADD media_type smallint NOT NULL default 0 AFTER image_id ;
