<?php
/**
 * Contains functions to assist with the system Date and Time formats. The formats
 * are how the admin chooses the date to appear on their site,
 *
 * @version  $Id$
 * @author   Matthew McNaney <matt@NOSPAM.tux.appstate.edu>
 * @package  Core
 */

class PHPWS_DateTime extends PHPWS_Template {

    /**
     * Month format
     * @var    string
     * @access private
     */
    public $date_month;

    /**
     * Day format
     * @var    string
     * @access private
     */
    public $date_day;

    /**
     * Year format
     * @var    string
     * @access private
     */
    public $date_year;

    /**
     * Day of the week format
     * @var    string
     * @access private
     */
    public $day_mode;

    /**
     * Day the week starts
     * 0 - Sunday
     * 1 - Monday
     * @var    string
     * @access private
     */
    public $day_start;

    /**
     * Order of the month, day and year with any other characters.
     * @var    string
     * @access private
     */
    public $date_order;

    /**
     * Format of the time display
     * @var    string
     * @access private
     */
    public $time_format;

    /**
     * Difference of time between server where viewed
     * @var    string
     * @access private
     */
    public $time_dif;

    /**
     * Contructor for the PHPWS_DateTime class
     *
     * @author Matthew McNaney <matt@NOSPAM.tux.appstate.edu>
     * @access public
     */
    public function loadDateTimeSettings() {
        if (file_exists($this->home_dir . "conf/dateSettings.en.php"))
        $dateFile = $this->home_dir . "conf/dateSettings.en.php";
        elseif(file_exists($this->source_dir . "conf/dateSettings.en.php"))
        $dateFile = $this->source_dir . "conf/dateSettings.en.php";
        else
        exit("Error: Unable to locate dateSettings file.<br />" . $this->source_dir . "conf/dateSettings.en.php");

        include($dateFile);

        $this->date_month  = $date_month;
        $this->date_day    = $date_day;
        $this->date_year   = $date_year;
        $this->day_mode    = $day_mode;
        $this->day_start   = $day_start;
        $this->date_order  = $date_order;
        $this->time_format = $time_format;
        $this->time_dif = $time_dif * 3600;
    }// END FUNC PHPWS_DateTime()

    /**
     * Formats all dates dependant on the admin settings
     *
     * This function can accept either a epoch time or a Unix timestamp.
     * It will return an array containing all the information needed for that
     * date in the formats specified by the site admin. If the data parameter is
     * blank, you will get back today's date and time.
     *
     * You can get what information you need from the returned array by
     * entering the proper index.
     *
     * September 28, 2002
     * Example: $today = $core->date(20020928010001, 1);
     * echo $today["weekday"]; // Prints Saturday
     * echo $today["time"]; // Prints 1:00am as setup
     *
     * @author Matthew McNaney <matt@NOSPAM.tux.appstate.edu>
     * @param  string  $data      Submitted date and time
     * @param  boolean $timestamp Indicates whether the data is a timestamp
     * @access public
     */
    public function date($data=NULL, $timestamp=FALSE){

        if ($timestamp){
            $y = substr($data, 0 , 4);
            $m = substr($data, 4, 2);
            $d = substr($data, 6, 2);
            $hour = substr($data, 8, 2);
            $minute = substr($data, 10, 2);
            $second = substr($data, 12, 2);
            $new_data = mktime($hour,$minute,$second,$m,$d,$y);
        } elseif ($data)
        $new_data = $data;
        else
        $new_data= time();

        $date["year"] = date($this->date_year, $new_data);
        $date["month"] = date($this->date_month, $new_data);
        $date["day"] = date($this->date_day, $new_data);
        $date["weekday"] = date("l", $new_data);
        $date["abbr_day"] = date("D", $new_data);
        $date["month_name"] = date("F", $new_data);
        $date["abbr_month"] = date("M", $new_data);
        $date["time"] = date($this->time_format, $new_data);
        $date["n_year"] = date("Y", $new_data);                            // Full numeric of year : 2001
        $date["n_month"] = date("n", $new_data);                           // Full numeric of month: 9
        $date["n_day"] = date("j", $new_data);                             // Full numeric of day  : 31
        $date["n_full"] = $date["n_year"].$date["n_month"].$date["n_day"]; // YearMonthDay         : 20010931
        $date["day_suf"] = $date["n_day"].date("S", $new_data);
        $date["n_week"] = date("w", $new_data);                            //number of weeks in the year
        $date["month_days"] = date("t", $new_data);

        // Week number
        // Number of week within year
        $year_start_day = mktime(0,0,0,1,1, $date["n_year"]);
        $year_start_wd = date("w", $year_start_day);
        $week_count_from = $year_start_day + ((7 - $year_start_wd + $this->day_start) * 86400);

        if ($this->day_start == $year_start_wd)
        $week = 1;
        else
        $week = 0;
        for ($i = $week_count_from; $i <= $new_data; $i += (86400 * 7)){
            $week++;
        }
        $date["week_num"] = $week;

        // Weekday number
        // Number of week the current day is in month

        $i = $j = NULL;
        $month_start_day = mktime(0,0,0,$date["n_month"], 1, $date["n_year"]);
        for ($i = $month_start_day; date("w", $i) != $date["n_week"]; $i += 86400);
        $wd_count = 0;
        for ($j = $i; $j <= $new_data; $j += 86400){
            if (date("w", $j) == $date["n_week"])
            $wd_count++;
        }

        $date["wd_count"] = $wd_count;

        $full = $this->date_order;
        $full = str_replace("y", $this->date_year, $full);
        $full = str_replace("m", $this->date_month, $full);
        $full = str_replace("d", $this->date_day, $full);
        $date["full"] = date($full, $new_data);
        return $date;

    }// END FUNC date()

    /**
     * Deconstructs a date and returns the unix epoch time
     *
     * Function does not use time. The format for the date must be YearMonthDay.
     * eg: 20020931
     *
     * @author Matthew McNaney<matt@NOSPAM.tux.appstate.edu>
     * @param  mixed   $date Date to be epoched
     * @return integer The epoched date
     * @access public
     */
    public function mkdate($date){
        $y = substr($date, 0, 4);
        $m = substr($date, 4, 2);
        $d = substr($date, 6, 2);

        return mktime(12,0,0, $m, $d, $y);
    }// END FUNC time()

    /**
     * Creates a month array for formDate
     *
     * @author                Matthew McNaney<matt@NOSPAM.tux.appstate.edu>
     * @return  array  month  Array of months 1 to 12
     * @access  public
     */
    public function monthArray(){
        for ($i=1; $i<13; $i++){
            $date = $this->date(mktime(2,0,0,$i,1,2000));
            $month[$i] = $date["month"];
        }
        return $month;
    }

    /**
     * Creates a day array for formDate
     *
     * @author                Matthew McNaney<matt@NOSPAM.tux.appstate.edu>
     * @return  array  month  Array of months 1 to 31
     * @access  public
     */
    public function dayArray(){
        for ($i=1; $i<32; $i++){
            $date = $this->date(mktime(0,0,0,1,$i));
            $day[$i] = $date["day"];
        }
        return $day;
    }

    /**
     * Creates a year array for formDate
     *
     * start defaults to this year if left blank. 10 year length
     * is the second default
     *
     * @author                  Matthew McNaney<matt@NOSPAM.tux.appstate.edu>
     * @param   integer start   Year to start from
     * @param   integer length  Amount to years to count to
     * @return  array   month   Array of years present to length
     * @access  public
     */
    public function yearArray($start=NULL, $length=10){
        if (!$start)
        $start = date("Y", time());

        for ($i=$start; $i<=$start+$length; $i++){
            $date = $this->date(mktime(0,0,0,1,1,$i));
            $year[$i] = $date["year"];
        }
        return $year;
    }



}// END CLASS PHPWS_DateTime
