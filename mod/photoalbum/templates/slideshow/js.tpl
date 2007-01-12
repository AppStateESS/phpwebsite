<script type="text/javascript">
//<![CDATA[

var slideImages       = new Array();
var slideWidths       = new Array();
var slideHeights      = new Array();
var slideImageNames   = new Array();
var slideImageBlurbs  = new Array();
var ieFilterLabel     = '{IE_FILTER_LABEL}';
var ieFilterField     = '{IE_FILTER_FIELD}';

var slideLoadedImages = 0;
var stopped           = 1;
var timer             = null;
var slideshowspeed    = 5000;
var speedIndex        = 2;
var whichlink         = 0;
var whichimage        = 0;
var loop              = true;
var waitingImage      = null;
var applyFilters      = 0;
var selectedFilter    = "";
var canBeFiltered     = false;

function countImages() {
   slideLoadedImages++;
}

function init() {
  formHandle = document.getElementById("slideshowForm");
  formHandle.loop.checked=true;
  formHandle.adjustSpeedField.selectedIndex=speedIndex;
  playControl();

  if(isIE()) {
    applyFilters = 1;
    ieFilterLabel = '&nbsp;&nbsp;<span class="smalltext">|</span>&nbsp;&nbsp;' + ieFilterLabel;

    setFilters(ieFilterLabel, ieFilterField);
    changeFilter();
  }

  slideit();
}

function setFilters(label, content) {
   element = document.getElementById('ieFilterLabel');
   element.innerHTML = label;

   element = document.getElementById('ieFilterFieldTpl');
   element.innerHTML = content;
}

function clearFilterFields() {
    setFilters("", "");
}

// preload images
function slideShowImages(){
  i=0;
  while(i < slideShowImages.arguments.length) {
     if(slideImages[i] != null) {
        if(!slideImages[i-1].complete)   
	    continue;
     }
      
     slideImages[i] = new Image();
     slideImages[i].onload = countImages;
     slideImages[i].src=slideShowImages.arguments[i];
     i++;
  }
}

// load image labels
function slideShowImageNames(){
  for (i=0;i<slideShowImageNames.arguments.length;i++){
    slideImageNames[i]=slideShowImageNames.arguments[i]
  }
}

// load image descriptions
function slideShowImageBlurbs(){
  for (i=0;i<slideShowImageBlurbs.arguments.length;i++){
    slideImageBlurbs[i]=slideShowImageBlurbs.arguments[i]
  }
}

// load image widths
function slideShowWidths() {
  for(i=0; i<slideShowWidths.arguments.length;i++) {
    slideWidths[i]=slideShowWidths.arguments[i]
  }
}

// load image heights
function slideShowHeights() {
  for(i=0; i<slideShowHeights.arguments.length;i++) {
    slideHeights[i]=slideShowHeights.arguments[i]
  }
}

function changeLoop() {
  loop = document.getElementById("loop").checked;

  if(loop == true) {
     startTimer();
   }
}

// adjust speed of slide show
function adjustSpeed() {
  currentField = document.getElementById("adjustSpeedField");
  currentIndex = currentField.selectedIndex;

  slideshowspeed = currentField.options[currentIndex].value; 

  clearTimer();
  startTimer();
}

function getFilterValue() {
  var newFilter =
    document.slideshowForm.ieFilterField.selectedIndex;

  return document.slideshowForm.ieFilterField.options[newFilter].value;
}

// change ie filter
function changeFilter() {
  selectedFilter = getFilterValue();
}

function isIE() {
   if(navigator.appName == "Microsoft Internet Explorer") 
	return true;
   else
	return false;
}

function changeText(elementID, newText, control) {

 if(!isIE()) {
  celement = document.getElementById(control);
  
  if(celement.hasChildNodes()) {
    element = celement.firstChild;
    // make sure type is text
    while(element.nodeType != 3 && element.hasChildNodes()) {
     element = element.firstChild;
    }
  }

  if(element.nodeType != 3) {
    var newSpan = document.createElement("span");
    var newText = document.createTextNode(newText);
    newSpan.appendChild(newText);

    celement = document.getElementById(control);
    element = document.getElementById(elementID);
    celement.replaceChild(newSpan,element);
  } else { 
    element.nodeValue = newText;
  }
 } else {
   element = document.getElementById(elementID);
   element.innerHTML = newText;
 }
}

function playControl() {
  changeText('startstop', '{PLAY_TEXT}', 'sscontrol');
}

function stopControl() {
  changeText('startstop', '{PAUSE_TEXT}', 'sscontrol');
}

// start-stop the slide show
function startStop() {
  if(slideImages.length == 0)
   return;

    if(stopped) {
      //play
      if(whichimage == 1) {
        whichimage = 0;
      }

      if(whichimage == 0)
        slideit();

      stopControl();
      stopped = 0;
      startTimer();
   } else {
      //stop
      playControl();
      stopped = 1;
      clearTimer();    
   }
}

// clear timer
function clearTimer() {
  clearTimeout(timer);
}

// starts timer
function startTimer() {
  if(timer != null) {
	clearTimeout(timer);
	timer = null;
  }

  if(stopped == 0)
    timer = setTimeout("slideit()", slideshowspeed)
}

//configure the paths of the images, plus corresponding target links
slideShowWidths({IMAGE_WIDTHS})
slideShowHeights({IMAGE_HEIGHTS})
slideShowImages({IMAGES})
slideShowImageNames({IMAGE_NAMES})
slideShowImageBlurbs({IMAGE_BLURBS})

function waitForNextImage() {
  document.images.slide.src={PRE_FILLER};
  document.images.slide.width=500;
  document.images.slide.height=500;

  if(whichimage == 0)
    changeText("imageNameText", "{LOADING_TXT}", "textcontroller")	
  else
    changeText("imageNameText", "{LOADING_NEXT_TXT}", "textcontroller")	

  waitingImage = setTimeout("slideit()", 50);
}

function clearWaitImage() {
  clearTimeout(waitingImage);
  waitingImage = null;
}

// advance to next image in slide show
function slideit(){
  if(slideImages.length == 0)
   return;

   if (!document.images)
    return

   if(slideWidths[whichimage] > 0 && slideHeights[whichimage] > 0) {
     if(!slideImages[whichimage].complete) {	
	clearTimer();
	waitForNextImage();
	return;
     }
   }

   if(waitingImage != null)
     clearWaitImage();

   if(!document.all) {
      document.images.slide = null;	
      document.images.slide = new Image();
   }	

   if(applyFilters) {

     if(document.images.slide && document.images.slide.style &&
	document.images.slide.style.filters){
	
	canBeFiltered = true;
	target = document.images.slide;
      }

      if(document.getElementById("slide")) {
	target = document.getElementById("slide");
	canBeFiltered = true;
      }

      if(canBeFiltered) {	
	target.style.filter=selectedFilter;
	if(target.filters && target.filters[0]) {
	  target.filters[0].Apply();
	  target.filters[0].Play();   
        }
      } else {
	applyFilters = false;
	clearFilterFields();
      }
   }

   document.images.slide.width=slideWidths[whichimage]
   document.images.slide.height=slideHeights[whichimage]
   document.images.slide.src=slideImages[whichimage].src

   changeText("imageNameText", slideImageNames[whichimage], "textcontrol")
   changeText("imageBlurbText", slideImageBlurbs[whichimage], "blurbcontrol")
   changeText("imageIndexInfo", "Image " + (whichimage+1) + " of " + slideImages.length, "indexcontrol")

   startTimer();

     if (whichimage < slideImages.length-1) {
          whichimage++;

     } else {
        whichimage = 0;

        if(loop == false)
          clearTimer();
     }
   }

//]]>
</script>
