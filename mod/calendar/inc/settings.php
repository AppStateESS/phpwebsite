<?php

/**
 * starting_day : day of the week to start calendars
 *                (0 - Sunday, 1 - Monday, etc.)
 * default_hour_format : uses date() settings (g, G, h, H)
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

$settings = array('use_calendar_style' => 1,
                  'starting_day'       => 0,
                  'personal_schedules' => 1,
                  'default_day_start'  => 8,
                  'default_day_end'    => 18,
                  'default_hour_format'=> 'g',
                  'display_mini'       => 2,
                  'info_panel'         => 0,
                  'public_schedule'    => -1,
                  'default_view'       => 'grid',
                  'allow_submissions'  => 0,
                  'mini_event_link'    => 0,
                  'cache_month_views'  => 0,
                  'brief_grid'         => 0,
                  'allow_public_ical'  => 1,
                  'mini_grid'          => 1,
                  'no_follow'          => 1,
                  'anon_ical'          => 1);
