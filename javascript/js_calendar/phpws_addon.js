/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

var pickroute = 0;
var phpws_url = '';

function forwardit(month,day)
{
    month = month - 0;
    day = day - 0;
    currentYear = currentYear - 0;
    url = phpws_url + '&m=' + month + '&d=' + day + '&y=' + currentYear;
    window.location.href = url;
}

function displayCalendarPick(month, day, year, url, buttonObj)
{
    calendarDisplayTime = false;
    phpws_url = url;
    pickroute = 1;
    currentYear = inputYear = year;
    currentDay = inputDay = day;
    currentMonth = inputMonth = month - 1;
  	 
    if(!calendarDiv){
        initCalendar();
    }else{
        writeCalendarContent();
    }
    //      returnFormat = format;
    returnDateTo = new Object();
    returnDateTo.value = '';
    positionCalendar(buttonObj);
    calendarDiv.style.visibility = 'visible';
    calendarDiv.style.display = 'block';
    if(iframeObj){
        iframeObj.style.display = '';
        iframeObj.style.height = '140px';
        iframeObj.style.width = '195px';
    }
    updateYearDiv();
    updateMonthDiv();
    calendarDisplayTime = false;
    setTimeProperties();
}
