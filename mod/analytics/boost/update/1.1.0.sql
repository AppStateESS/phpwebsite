CREATE TABLE analytics_tracker_google (
    id      INTEGER NOT NULL PRIMARY KEY,
    account VARCHAR(255)
);

CREATE TABLE analytics_tracker_piwik (
    id        INTEGER NOT NULL PRIMARY KEY,
    piwik_url VARCHAR(255),
    piwik_id  INTEGER
);

CREATE TABLE analytics_tracker_owa (
    id                  INTEGER NOT NULL PRIMARY KEY,
    owa_url             VARCHAR(255),
    owa_site_id         VARCHAR(32),
    owa_track_page_view SMALLINT,
    owa_track_clicks    SMALLINT,
    owa_track_domstream SMALLINT
);
