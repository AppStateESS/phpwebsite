BEGIN:VCALENDAR
PRODID:phpWebSite-Calendar
VERSION:2.0
<!-- BEGIN event -->
BEGIN:VEVENT DTSTAMP:{TODAY} ORGANIZER:MAILTO:{EMAIL} UID:{UID}
SUMMARY:{SUMMARY}
<!-- BEGIN description -->
DESCRIPTION:{DESCRIPTION}
<!-- END description -->
<!-- BEGIN location -->
LOCATION:{LOCATION}
<!-- END location -->
<!-- BEGIN dtstart_end -->
DTSTART:{DTSTART}
DTEND:{DTEND}
<!-- END dtstart_end -->
<!-- BEGIN all_day -->
DTSTART;VALUE=DATE:{DTSTART_AD}
DTEND;VALUE=DATE:{DTEND_AD}
<!-- END all_day -->
END:VEVENT
<!-- END event -->
END:VCALENDAR{EMPTY}
