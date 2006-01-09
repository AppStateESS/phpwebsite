/*
(C) www.dhtmlgoodies.com, September 2005

Version 1.2, November 8th - 2005 - Added <iframe> background in IE
Version 1.3, November 12th - 2005 - Fixed top bar position in Opera 7
Version 1.4, December 28th - 2005 - Support for Spanish and Portuguese

This is a script from www.dhtmlgoodies.com. You will find this and a lot of other scripts at our website.	

Terms of use:
You are free to use this script as long as the copyright message is kept intact. However, you may not
redistribute, sell or repost it without our permission.

Thank you!

www.dhtmlgoodies.com
Alf Magne Kalleland

*/

     /**
        Notice:
        Some changes where made to this script to work specifically with phpWebSite.
        Any additions have been noted in the code.
      */

var pickroute = 0;
var phpws_url = '';

var languageCode = 'en';	// Possible values: 	en,ge,no,nl,es,pt-br,fr	

							
var calendar_offsetTop = 0;		// Offset - calendar placement - You probably have to modify this value if you're not using a strict doctype
var calendar_offsetLeft = 0;	// Offset - calendar placement - You probably have to modify this value if you're not using a strict doctype
var calendarDiv = false;

var MSIE = false;
var Opera = false;
if(navigator.userAgent.indexOf('MSIE')>=0 && navigator.userAgent.indexOf('Opera')<0)MSIE=true;
if(navigator.userAgent.indexOf('Opera')>=0)Opera=true;


switch(languageCode){
	case "en":	/* English */
		var monthArray = ['January','February','March','April','May','June','July','August','September','October','November','December'];
		var monthArrayShort = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
		var dayArray = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
		var weekString = 'Week';
		var todayString = 'Today is';
		break;
	case "ge":	/* German */
		var monthArray = ['Jänner','Februar','März','April','Mai','Juni','Juli','August','September','Oktober','November','Dezember'];
		var monthArrayShort = ['Jan','Feb','Mar','Apr','Mai','Jun','Jul','Aug','Sep','Okt','Nov','Dez'];
		var dayArray = ['Mon','Die','Mit','Don','Fre','Sam','Son'];	
		var weekString = 'Woche';
		var todayString = 'Heute';		
		break;
	case "no":	/* Norwegian */
		var monthArray = ['Januar','Februar','Mars','April','Mai','Juni','Juli','August','September','Oktober','November','Desember'];
		var monthArrayShort = ['Jan','Feb','Mar','Apr','Mai','Jun','Jul','Aug','Sep','Okt','Nov','Des'];
		var dayArray = ['Man','Tir','Ons','Tor','Fre','L&oslash;r','S&oslash;n'];	
		var weekString = 'Uke';
		var todayString = 'Dagen i dag er';
		break;	
	case "nl":	/* Dutch */
		var monthArray = ['Januari','Februari','Maart','April','Mei','Juni','Juli','Augustus','September','Oktober','November','December'];
		var monthArrayShort = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Aug','Sep','Okt','Nov','Dec'];
		var dayArray = ['Ma','Di','Wo','Do','Vr','Za','Zo'];
		var weekString = 'Week';
		var todayString = 'Vandaag';
		break;	
	case "es": /* Spanish */
		var monthArray = ['Enero','Febrero','Marzo','April','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
		var monthArrayShort =['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
		var dayArray = ['Lun','Mar','Mie','Jue','Vie','Sab','Dom'];
		var weekString = 'Semana';
		var todayString = 'Hoy es';
		break; 	
	case "pt-br":  /* Brazilian portuguese (pt-br) */
		var monthArray = ['Janeiro','Fevereiro','Mar&ccedil;o','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
		var monthArrayShort = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
		var dayArray = ['Seg','Ter','Qua','Qui','Sex','S&aacute;b','Dom'];
		var weekString = 'Sem.';
		var todayString = 'Hoje &eacute;';
		break;
	case "fr":      /* French */
		var monthArray = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];		
		var monthArrayShort = ['Jan','Fev','Mar','Avr','Mai','Jun','Jul','Aou','Sep','Oct','Nov','Dec'];
		var dayArray = ['Lun','Mar','Mer','Jeu','Ven','Sam','Dim'];
		var weekString = 'Sem';
		var todayString = "Aujourd'hui";
		break; 						
}



var daysInMonthArray = [31,28,31,30,31,30,31,31,30,31,30,31];
var currentMonth;
var currentYear;
var calendarContentDiv;
var returnDateTo;
var returnFormat;
var activeSelectBoxMonth;
var activeSelectBoxYear;
var iframeObj = false;

var returnDateToYear;
var returnDateToMonth;
var returnDateToDay;

var inputYear;
var inputMonth;
var inputDay;


var selectBoxHighlightColor = '#D60808'; // Highlight color of select boxes
var selectBoxRolloverBgColor = '#E2EBED'; // Background color on drop down lists(rollover)
function cancelCalendarEvent()
{
	return false;
}
function isLeapYear(inputYear)
{
	if(inputYear%400==0||(inputYear%4==0&&inputYear%100!=0)) return true;
	return false;	
	
}
var activeSelectBoxMonth = false;

function highlightMonthYear()
{
	if(activeSelectBoxMonth)activeSelectBoxMonth.className='';
	
	if(this.className=='monthYearActive'){
		this.className='';	
	}else{
		this.className = 'monthYearActive';
		activeSelectBoxMonth = this;
	}
}

function showMonthDropDown()
{
	if(document.getElementById('monthDropDown').style.display=='block'){
		document.getElementById('monthDropDown').style.display='none';	
	}else{
		document.getElementById('monthDropDown').style.display='block';		
		document.getElementById('yearDropDown').style.display='none';
	}
}

function showYearDropDown()
{
	if(document.getElementById('yearDropDown').style.display=='block'){
		document.getElementById('yearDropDown').style.display='none';	
	}else{
		document.getElementById('yearDropDown').style.display='block';	
		document.getElementById('monthDropDown').style.display='none';	
	}		

}

function selectMonth()
{
	document.getElementById('calendar_month_txt').innerHTML = this.innerHTML
	currentMonth = this.id.replace(/[^\d]/g,'');

	document.getElementById('monthDropDown').style.display='none';
	for(var no=0;no<monthArray.length;no++){
		document.getElementById('monthDiv_'+no).style.color='';	
	}
	this.style.color = selectBoxHighlightColor;
	activeSelectBoxMonth = this;
	writeCalendarContent();
	
}

function selectYear()
{
	document.getElementById('calendar_year_txt').innerHTML = this.innerHTML
	currentYear = this.innerHTML.replace(/[^\d]/g,'');
	document.getElementById('yearDropDown').style.display='none';
	if(activeSelectBoxYear){
		activeSelectBoxYear.style.color='';
	}
	activeSelectBoxYear=this;
	this.style.color = selectBoxHighlightColor;
	writeCalendarContent();
	
}

function switchMonth()
{
	if(this.src.indexOf('left')>=0){
		currentMonth=currentMonth-1;;
		if(currentMonth<0){
			currentMonth=11;
			currentYear=currentYear-1;
		}
	}else{
		currentMonth=currentMonth+1;;
		if(currentMonth>11){
			currentMonth=0;
			currentYear=currentYear/1+1;
		}	
	}	
	
	writeCalendarContent();	
	
	
}

function createMonthDiv(){
	var div = document.createElement('DIV');
	div.className='monthYearPicker';
	div.id = 'monthPicker';
	
	for(var no=0;no<monthArray.length;no++){
		var subDiv = document.createElement('DIV');
		subDiv.innerHTML = monthArray[no];
		subDiv.onmouseover = highlightMonthYear;
		subDiv.onmouseout = highlightMonthYear;
		subDiv.onclick = selectMonth;
		subDiv.id = 'monthDiv_' + no;
		subDiv.style.width = '56px';
		subDiv.onselectstart = cancelCalendarEvent;		
		div.appendChild(subDiv);
		if(currentMonth && currentMonth==no){
			subDiv.style.color = selectBoxHighlightColor;
			activeSelectBoxMonth = subDiv;
		}				
		
	}	
	return div;
	
}

function changeSelectBoxYear()
{

	var yearItems = this.parentNode.getElementsByTagName('DIV');
	if(this.innerHTML.indexOf('-')>=0){
		var startYear = yearItems[1].innerHTML/1 -1;
		if(activeSelectBoxYear){
			activeSelectBoxYear.style.color='';
		}
	}else{
		var startYear = yearItems[1].innerHTML/1 +1;
		if(activeSelectBoxYear){
			activeSelectBoxYear.style.color='';

		}			
	}

	for(var no=1;no<yearItems.length-1;no++){
		yearItems[no].innerHTML = startYear+no-1;	
		yearItems[no].id = 'yearDiv' + (startYear/1+no/1-1);	
		
	}		
	if(activeSelectBoxYear){
		activeSelectBoxYear.style.color='';
		if(document.getElementById('yearDiv'+currentYear)){
			activeSelectBoxYear = document.getElementById('yearDiv'+currentYear);
			activeSelectBoxYear.style.color=selectBoxHighlightColor;;
		}
	}
}

function updateYearDiv()
{
	var div = document.getElementById('yearDropDown');
	var yearItems = div.getElementsByTagName('DIV');
	for(var no=1;no<yearItems.length-1;no++){
		yearItems[no].innerHTML = currentYear/1 -6 + no;	
		if(currentYear==(currentYear/1 -6 + no)){
			yearItems[no].style.color = selectBoxHighlightColor;
			activeSelectBoxYear = yearItems[no];				
		}else{
			yearItems[no].style.color = '';
		}
	}		
}

function updateMonthDiv()
{
	for(no=0;no<12;no++){
		document.getElementById('monthDiv_' + no).style.color = '';
	}		
	document.getElementById('monthDiv_' + currentMonth).style.color = selectBoxHighlightColor;
	activeSelectBoxMonth = 	document.getElementById('monthDiv_' + currentMonth);
}

function createYearDiv()
{

	if(!document.getElementById('yearDropDown')){
		var div = document.createElement('DIV');
		div.className='monthYearPicker';
	}else{
		var div = document.getElementById('yearDropDown');
		var subDivs = div.getElementsByTagName('DIV');
		for(var no=0;no<subDivs.length;no++){
			subDivs[no].parentNode.removeChild(subDivs[no]);	
		}	
	}	
	
	
	var d = new Date();
	if(currentYear){
		d.setFullYear(currentYear);	
	}

	var startYear = d.getFullYear()/1 - 5;

	
	var subDiv = document.createElement('DIV');
	subDiv.innerHTML = '&nbsp;&nbsp;- ';
	subDiv.onclick = changeSelectBoxYear;
	subDiv.onmouseover = highlightMonthYear;
	subDiv.onmouseout = highlightMonthYear;	
	subDiv.onselectstart = cancelCalendarEvent;			
	div.appendChild(subDiv);
	
	for(var no=startYear;no<(startYear+10);no++){
		var subDiv = document.createElement('DIV');
		subDiv.innerHTML = no;
		subDiv.onmouseover = highlightMonthYear;
		subDiv.onmouseout = highlightMonthYear;		
		subDiv.onclick = selectYear;		
		subDiv.id = 'yearDiv' + no;	
		subDiv.onselectstart = cancelCalendarEvent;	
		div.appendChild(subDiv);
		if(currentYear && currentYear==no){
			subDiv.style.color = selectBoxHighlightColor;
			activeSelectBoxYear = subDiv;
		}			
	}
	var subDiv = document.createElement('DIV');
	subDiv.innerHTML = '&nbsp;&nbsp;+ ';
	subDiv.onclick = changeSelectBoxYear;
	subDiv.onmouseover = highlightMonthYear;
	subDiv.onmouseout = highlightMonthYear;		
	subDiv.onselectstart = cancelCalendarEvent;			
	div.appendChild(subDiv);		

	
	return div;
}

function highlightSelect()
{
	if(this.className=='selectBox'){
		this.className = 'selectBoxOver';	
		this.getElementsByTagName('IMG')[0].src = 'javascript/js_calendar/images/down_over.gif';
	}else{
		this.className = 'selectBox';	
		this.getElementsByTagName('IMG')[0].src = 'javascript/js_calendar/images/down.gif';			
	}
	
}

function highlightArrow()
{
	if(this.src.indexOf('over')>=0){
		if(this.src.indexOf('left')>=0)this.src = 'javascript/js_calendar/images/left.gif';	
		if(this.src.indexOf('right')>=0)this.src = 'javascript/js_calendar/images/right.gif';				
	}else{
		if(this.src.indexOf('left')>=0)this.src = 'javascript/js_calendar/images/left_over.gif';	
		if(this.src.indexOf('right')>=0)this.src = 'javascript/js_calendar/images/right_over.gif';	
	}
}

function highlightClose()
{
	if(this.src.indexOf('over')>=0){
		this.src = 'javascript/js_calendar/images/close.gif';
	}else{
		this.src = 'javascript/js_calendar/images/close_over.gif';	
	}	

}

function closeCalendar(){

	document.getElementById('yearDropDown').style.display='none';
	document.getElementById('monthDropDown').style.display='none';
		
	calendarDiv.style.display='none';
	if(iframeObj)iframeObj.style.display='none';
	if(activeSelectBoxMonth)activeSelectBoxMonth.className='';
	if(activeSelectBoxYear)activeSelectBoxYear.className='';
}

function writeTopBar()
{

	var topBar = document.createElement('DIV');
	topBar.className = 'topBar';
	topBar.id = 'topBar';
	calendarDiv.appendChild(topBar);
	
	// Left arrow
	var leftDiv = document.createElement('DIV');
	leftDiv.style.marginRight = '1px';
	var img = document.createElement('IMG');
	img.src = 'javascript/js_calendar/images/left.gif';
	img.onmouseover = highlightArrow;
	img.onclick = switchMonth;
	img.onmouseout = highlightArrow;
	leftDiv.appendChild(img);	
	topBar.appendChild(leftDiv);
	if(Opera)leftDiv.style.width = '16px';
	
	// Right arrow
	var rightDiv = document.createElement('DIV');
	rightDiv.style.marginRight = '1px';
	var img = document.createElement('IMG');
	img.src = 'javascript/js_calendar/images/right.gif';
	img.onclick = switchMonth;
	img.onmouseover = highlightArrow;
	img.onmouseout = highlightArrow;
	rightDiv.appendChild(img);
	if(Opera)rightDiv.style.width = '16px';
	topBar.appendChild(rightDiv);		

			
	// Month selector
	var monthDiv = document.createElement('DIV');
	monthDiv.id = 'monthSelect';
	monthDiv.onmouseover = highlightSelect;
	monthDiv.onmouseout = highlightSelect;
	monthDiv.onclick = showMonthDropDown;
	var span = document.createElement('SPAN');		
	span.innerHTML = monthArray[currentMonth];
	span.id = 'calendar_month_txt';
	monthDiv.appendChild(span);

	var img = document.createElement('IMG');
	img.src = 'javascript/js_calendar/images/down.gif';
	img.style.position = 'absolute';
	img.style.right = '0px';
	monthDiv.appendChild(img);
	monthDiv.className = 'selectBox';
	if(Opera){
		img.style.cssText = 'float:right;position:relative';
		img.style.position = 'relative';
		img.style.styleFloat = 'right';
	}
	topBar.appendChild(monthDiv);

	var monthPicker = createMonthDiv();
	monthPicker.style.left = '37px';
	monthPicker.style.top = monthDiv.offsetTop + monthDiv.offsetHeight + 1 + 'px';
	monthPicker.style.width ='60px';
	monthPicker.id = 'monthDropDown';
	
	calendarDiv.appendChild(monthPicker);
			
	// Year selector
	var yearDiv = document.createElement('DIV');
	yearDiv.onmouseover = highlightSelect;
	yearDiv.onmouseout = highlightSelect;
	yearDiv.onclick = showYearDropDown;
	var span = document.createElement('SPAN');		
	span.innerHTML = currentYear;
	span.id = 'calendar_year_txt';
	yearDiv.appendChild(span);
	topBar.appendChild(yearDiv);
	
	var img = document.createElement('IMG');
	img.src = 'javascript/js_calendar/images/down.gif';
	yearDiv.appendChild(img);
	yearDiv.className = 'selectBox';
	
	if(Opera){
		yearDiv.style.width = '50px';
		img.style.cssText = 'float:right';
		img.style.position = 'relative';
		img.style.styleFloat = 'right';
	}	
	
	var yearPicker = createYearDiv();
	yearPicker.style.left = '113px';
	yearPicker.style.top = monthDiv.offsetTop + monthDiv.offsetHeight + 1 + 'px';
	yearPicker.style.width = '35px';
	yearPicker.id = 'yearDropDown';
	calendarDiv.appendChild(yearPicker);
	
		
	var img = document.createElement('IMG');
	img.src = 'javascript/js_calendar/images/close.gif';
	img.style.styleFloat = 'right';

	img.onmouseover = highlightClose;
	img.onmouseout = highlightClose;

	img.onclick = closeCalendar;
	topBar.appendChild(img);
	if(!document.all){
		img.style.position = 'absolute';
		img.style.right = '2px';
	}
	
	

}

function writeCalendarContent()
{
	var calendarContentDivExists = true;
	if(!calendarContentDiv){
		calendarContentDiv = document.createElement('DIV');
		calendarDiv.appendChild(calendarContentDiv);
		calendarContentDivExists = false;
	}
	
	var d = new Date();		
	d.setMonth(currentMonth);
	d.setFullYear(currentYear);		
	d.setDate(0);		


	document.getElementById('calendar_year_txt').innerHTML = currentYear;
	document.getElementById('calendar_month_txt').innerHTML = monthArray[currentMonth];
	
	var existingTable = calendarContentDiv.getElementsByTagName('TABLE');
	if(existingTable.length>0){
		calendarContentDiv.removeChild(existingTable[0]);
	}
	
	var calTable = document.createElement('TABLE');
	calTable.cellSpacing = '0';
	calendarContentDiv.appendChild(calTable);
	var calTBody = document.createElement('TBODY');
	calTable.appendChild(calTBody);
	var row = calTBody.insertRow(-1);
	var cell = row.insertCell(-1);
	cell.innerHTML = weekString;
	cell.style.backgroundColor = selectBoxRolloverBgColor;
	
	for(var no=0;no<dayArray.length;no++){
		var cell = row.insertCell(-1);
		cell.innerHTML = dayArray[no]; 
	}
	
	var row = calTBody.insertRow(-1);
	var cell = row.insertCell(-1);
	cell.style.backgroundColor = selectBoxRolloverBgColor;
	var week = getWeek(currentYear,currentMonth,1);
	cell.innerHTML = week;		// Week
	for(var no=0;no<d.getDay();no++){
		var cell = row.insertCell(-1);
		cell.innerHTML = '&nbsp;';
	}

	var colCounter = d.getDay();
	var daysInMonth = daysInMonthArray[currentMonth];
	if(daysInMonth==28){
		if(isLeapYear(currentYear))daysInMonth=29;
	}
	
	for(var no=1;no<=daysInMonth;no++){
		d.setDate(no-1);
		if(colCounter>0 && colCounter%7==0){
			var row = calTBody.insertRow(-1);
			var cell = row.insertCell(-1);
			var week = getWeek(currentYear,currentMonth,no);
			cell.innerHTML = week;		// Week	
			cell.style.backgroundColor = selectBoxRolloverBgColor;			
		}
		var cell = row.insertCell(-1);
		if(currentYear==inputYear && currentMonth == inputMonth && no==inputDay){
			cell.style.color = '#FF0000';	
		}
		cell.innerHTML = no;
		cell.onclick = pickDate;
		colCounter++;
	}
	
	
	if(!document.all){
		if(calendarContentDiv.offsetHeight)
			document.getElementById('topBar').style.top = calendarContentDiv.offsetHeight + document.getElementById('topBar').offsetHeight -1 + 'px';
		else{
			document.getElementById('topBar').style.top = '';
			document.getElementById('topBar').style.bottom = '0px';
		}
			
	}
	
	if(iframeObj){
		if(!calendarContentDivExists)setTimeout('resizeIframe()',350);else setTimeout('resizeIframe()',10);
	}
		
	
}

function resizeIframe()
{
	iframeObj.style.width = calendarDiv.offsetWidth + 'px';
	iframeObj.style.height = calendarDiv.offsetHeight + 'px' ;	
	
	
}

function pickDate()
{
	var month = currentMonth/1 +1;
	if(month<10)month = '0' + month;
	var day = this.innerHTML;
	if(day/1<10)day = '0' + day;

        // pickroute added specifically for phpwebsite
        if (pickroute) {
            month = month - 0;
            day = day - 0;
            currentYear = currentYear - 0;
            url = phpws_url + '&m=' + month + '&d=' + day + '&y=' + currentYear;
            window.location.href = url;
        } else if(returnFormat){
		returnFormat = returnFormat.replace('dd',day);
		returnFormat = returnFormat.replace('mm',month);
		returnFormat = returnFormat.replace('yyyy',currentYear);
		returnDateTo.value = returnFormat;
	}else{
		for(var no=0;no<returnDateToYear.options.length;no++){
			if(returnDateToYear.options[no].value==currentYear){
				returnDateToYear.selectedIndex=no;
				break;
			}				
		}
		for(var no=0;no<returnDateToMonth.options.length;no++){
			if(returnDateToMonth.options[no].value==month){
				returnDateToMonth.selectedIndex=no;
				break;
			}				
		}
		for(var no=0;no<returnDateToDay.options.length;no++){
			if(returnDateToDay.options[no].value==day){
				returnDateToDay.selectedIndex=no;
				break;
			}				
		}
		
		
	}
	closeCalendar();
	
}

// This function is from http://www.codeproject.com/csharp/gregorianwknum.asp
// Only changed the month add
function getWeek(year,month,day){
	day = day/1;
	year = year /1;
    month = month/1 + 1; //use 1-12
    var a = Math.floor((14-(month))/12);
    var y = year+4800-a;
    var m = (month)+(12*a)-3;
    var jd = day + Math.floor(((153*m)+2)/5) + 
                 (365*y) + Math.floor(y/4) - Math.floor(y/100) + 
                 Math.floor(y/400) - 32045;      // (gregorian calendar)
    var d4 = (jd+31741-(jd%7))%146097%36524%1461;
    var L = Math.floor(d4/1460);
    var d1 = ((d4-L)%365)+L;
    NumberOfWeek = Math.floor(d1/7) + 1;
    return NumberOfWeek;        
}

function writeBottomBar()
{
	var d = new Date();
	var topBar = document.createElement('DIV');
	topBar.id = 'topBar';
	topBar.className = 'todaysDate';
	topBar.innerHTML = todayString + ': ' + d.getDate() + '.' + monthArrayShort[d.getMonth()] + '. ' +  d.getFullYear() ;
	calendarDiv.appendChild(topBar);	
	
	
		
}
function getTopPos(inputObj)
{
	
  var returnValue = inputObj.offsetTop + inputObj.offsetHeight;
  while((inputObj = inputObj.offsetParent) != null)returnValue += inputObj.offsetTop;
  return returnValue + calendar_offsetTop;
}

function getleftPos(inputObj)
{
  var returnValue = inputObj.offsetLeft;
  while((inputObj = inputObj.offsetParent) != null)returnValue += inputObj.offsetLeft;
  return returnValue + calendar_offsetLeft;
}

function positionCalendar(inputObj)
{
	calendarDiv.style.left = getleftPos(inputObj) + 'px';
	calendarDiv.style.top = getTopPos(inputObj) + 'px';
	if(iframeObj){
		iframeObj.style.left = calendarDiv.style.left;
		iframeObj.style.top =  calendarDiv.style.top;
	}
		
}
	
function initCalendar()
{
	if(MSIE){
		iframeObj = document.createElement('IFRAME');
		iframeObj.style.position = 'absolute';
		iframeObj.border='0px';
		iframeObj.style.border = '0px';
		iframeObj.style.backgroundColor = '#FF0000';
		document.body.appendChild(iframeObj);
	}
		
	calendarDiv = document.createElement('DIV');	
	calendarDiv.id = 'calendarDiv';
	calendarDiv.style.zIndex = 1000;
	
	document.body.appendChild(calendarDiv);
	writeBottomBar();
	writeTopBar();
	

	
	if(!currentYear){
		var d = new Date();
		currentMonth = d.getMonth();
		currentYear = d.getFullYear();
	}
	writeCalendarContent();	


		
}

/* This code is specific to phpWebsite and was written by
 * Matthew McNaney
 */
function displayCalendarPick(month, day, year, url, buttonObj)
{
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
        //	returnFormat = format;
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
}


function displayCalendar(inputField,format,buttonObj)
{
    if (!inputField.value.match(/\d{4}\/\d{2}\/\d{2}/)) {
        var d = new Date();

        currentMonth = d.getMonth() + 1;
        currentYear = d.getFullYear();
        currentDay = d.getDay() + 1; 

        if (currentMonth < 10) {
            currentMonth = '0'+currentMonth;
        }

        if (currentDay < 10) {
            currentDay = '0'+currentDay;
        }
        inputField.value = currentYear + '/' + currentMonth + '/' + currentDay;
        alert('YYYY/MM/DD');
        return;
    }

	if(inputField.value.length==format.length){
		var monthPos = format.indexOf('mm');
		currentMonth = inputField.value.substr(monthPos,2)/1 -1;	
		var yearPos = format.indexOf('yyyy');
		currentYear = inputField.value.substr(yearPos,4);		
		var dayPos = format.indexOf('dd');
		tmpDay = inputField.value.substr(dayPos,2);	
	}else{
		var d = new Date();
		currentMonth = d.getMonth();
		currentYear = d.getFullYear();
		tmpDay = 1;
	}
	
	inputYear = currentYear;
	inputMonth = currentMonth;
	inputDay = tmpDay/1;
	
	if(!calendarDiv){
		initCalendar();			
	}else{
            writeCalendarContent();
	}			
	returnFormat = format;
	returnDateTo = inputField;
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
	
}

function displayCalendarSelectBox(yearInput,monthInput,dayInput,buttonObj)
{
	currentMonth = monthInput.options[monthInput.selectedIndex].value/1-1;
	currentYear = yearInput.options[yearInput.selectedIndex].value;

	inputYear = yearInput.options[yearInput.selectedIndex].value;
	inputMonth = monthInput.options[monthInput.selectedIndex].value/1 - 1;
	inputDay = dayInput.options[dayInput.selectedIndex].value/1;
			
	if(!calendarDiv){
		initCalendar();			
	}else{
		writeCalendarContent();
	}		

	returnDateToYear = yearInput;
	returnDateToMonth = monthInput;
	returnDateToDay = dayInput;
	

	

	
	returnFormat = false;
	returnDateTo = false;
	positionCalendar(buttonObj);
	calendarDiv.style.visibility = 'visible';	
	calendarDiv.style.display = 'block';
	if(iframeObj){
		iframeObj.style.display = '';
		iframeObj.style.height = calendarDiv.offsetHeight + 'px';
		iframeObj.style.width = calendarDiv.offsetWidth + 'px';	
	}
	updateYearDiv();
	updateMonthDiv();
		
}
