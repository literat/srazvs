//	Datedit v1.0.0
//	by Ivo Skalicky - ivo.skalicky@itpro.cz
//  ITPro CZ - http://www.itpro.cz
//
//	For more information on this script, visit:
//	http://itpro.cz/datedit/
//
//	Licensed under the Creative Commons Attribution 3.0 License - http://creativecommons.org/licenses/by/3.0/

/*----------------------------------------------------------------------------*/

// config options
var datedit_DEFAULT_FORMAT = "yyyy-mm-dd";
var datedit_DEFAULT_ENABLE_EDIT = true;
var datedit_BUTTON_WIDTH = 22;
var datedit_VALIDATE_OUTPUT = true;
var datedit_WEEK_STARTS = 0; // 0 = Sunday, 1 = Monday, ...

var datedit_USE_ANIMATION = true;
var datedit_ANIMATION_STEPS = 8;
var datedit_ANIMATION_SPEED = 40;
/*----------------------------------------------------------------------------*/

// default language costants
var datedit_BUTTON_TEXT = "calendar";
var datedit_BUTTON_HINT = "Click to display calendar";
var datedit_MONTH_NAMES = Array("January","February","March","April","May","June","July","August","September","October","November","December");
var datedit_DAY_NAMES = Array("Su","Mo","Tu","We","Th","Fr","Sa");
var datedit_NEXT_MONTH = "Next month";
var datedit_PREV_MONTH = "Previous month";
var datedit_CHANGE_MONTH = "Change month";
var datedit_CHANGE_YEAR = "Change year";
var datedit_YEAR_DIFF = Array(-25,-10,-5,-3,-2,-1,0,1,2,3,5,10,25);
var datedit_YEAR_DIFF_WORD = Array("years","years","years","years","years","year","year","year","years","years","years","years","years");
var datedit_MONTH_HEAD = "Month";
var datedit_YEAR_HEAD = "Year";
var datedit_INVALID_DATE_FORMAT = "Date is not valid";

/*----------------------------------------------------------------------------*/

// internal global variables
var datedit_ELEMENTS = new Array();
var datedit_ROOT;
var datedit_DIV;
var datedit_MONTH_SELECTOR;
var datedit_YEAR_SELECTOR;

var datedit_DAY;
var datedit_MONTH;
var datedit_YEAR;
var datedit_DAY_SEL;
var datedit_MONTH_SEL;
var datedit_YEAR_SEL;
var datedit_HOUR;
var datedit_MINUTE;
var datedit_SECOND;
var datedit_ACTIVE_INPUT;
var datedit_ACTIVE_FORMAT;
var datedit_ANIMATION = new Array();

/*----------------------------------------------------------------------------*/

function is_outside_root(element,root) {
  if (element.className == 'datedit') return false;
  while (element.parentNode) {
    if (element==root) return false;
    element = element.parentNode;
  }
  return true;
} 

/*----------------------------------------------------------------------------*/

function datedit_is_opera() {
  var agent=navigator.userAgent.toLowerCase();
  return (agent.indexOf("opera")>=0);
}

function datedit_is_ie() {
  var agent=navigator.userAgent.toLowerCase();
  return ((agent.indexOf("msie") != -1) && (agent.indexOf("opera") == -1));
}

/*----------------------------------------------------------------------------*/

function global_click(e) {
  
  var target = (e && e.target) || (event && event.srcElement);
  if (is_outside_root(target,datedit_ROOT) && target.className.indexOf('navig')<0) {
    datedit_hide();
  } else {
    if (!is_outside_root(target,datedit_DIV) && target.tagName != 'A') {
      if (datedit_MONTH_SELECTOR.style.visibility=='visible') datedit_monthsel_hide();
      if (datedit_YEAR_SELECTOR.style.visibility=='visible') datedit_yearsel_hide();
    }
  }
} 

/*----------------------------------------------------------------------------*/

/* function adds datedit element info */
function datedit(elementId,format,enableEdit,ioFormat) { 
  var element = new Array(elementId,format,enableEdit,ioFormat);
  if (format==undefined) element[1] = datedit_DEFAULT_FORMAT;
  if (enableEdit==undefined) element[2] = datedit_DEFAULT_ENABLE_EDIT;
  if (ioFormat==undefined) element[3] = null;
  
  // check if format is valid
  var res = datedit_format2regexp(element[1]);
  if (res[1]<0 || res[2]<0 || res[3]<0) {
    window.alert("ERROR: Invalid format!\n"+element[1]);
  } else {
    datedit_ELEMENTS.push(element);
  }
}



/*----------------------------------------------------------------------------*/

function datedit_yearsel_show(year) {
  
  if (datedit_MONTH_SELECTOR.style.visibility=='visible') datedit_monthsel_hide();  
    
  // get position of month name
	var pos = get_position(year);
	 
  for (var i=0;i<datedit_YEAR_SELECTOR.childNodes.length-1;i++) {
	  var li = datedit_YEAR_SELECTOR.childNodes[i+1];
    li.className = (datedit_YEAR_DIFF[i]==0)?'sel':'';
    var html = '<a href="#" onclick="return datedit_change_year('+(datedit_YEAR+datedit_YEAR_DIFF[i])+');">'+(datedit_YEAR+datedit_YEAR_DIFF[i]);
    if (datedit_YEAR_DIFF[i]!=0) {
      html += '<span>('+((datedit_YEAR_DIFF[i]>0)?'+':'')+datedit_YEAR_DIFF[i]+' '+datedit_YEAR_DIFF_WORD[i]+')</span>';
    }
    html += '</a>';
    li.innerHTML = html;
	}
  
  datedit_YEAR_SELECTOR.style.left = (pos[0]+year.offsetWidth/2-datedit_YEAR_SELECTOR.offsetWidth/2)+'px';
  datedit_YEAR_SELECTOR.style.top = (pos[1]+2)+'px';
  datedit_show_element(datedit_YEAR_SELECTOR);
    
  return false;

}

/*----------------------------------------------------------------------------*/

function datedit_monthsel_show(month) {
  
  if (datedit_YEAR_SELECTOR.style.visibility=='visible') datedit_yearsel_hide();

  
  // get position of month name
	var pos = get_position(month);
		
	for (var i=1;i<datedit_MONTH_SELECTOR.childNodes.length;i++) {
	  var li = datedit_MONTH_SELECTOR.childNodes[i]; 
    li.className=(i==datedit_MONTH)?'sel':'';
  }
	
  datedit_MONTH_SELECTOR.style.left = (pos[0]+month.offsetWidth/2-datedit_MONTH_SELECTOR.offsetWidth/2)+'px';
  datedit_MONTH_SELECTOR.style.top = (pos[1]+2)+'px';
	datedit_show_element(datedit_MONTH_SELECTOR);
	return false;
}

/*----------------------------------------------------------------------------*/

function datedit_monthsel_hide() {
  datedit_hide_element(datedit_MONTH_SELECTOR);
}

/*----------------------------------------------------------------------------*/

function datedit_yearsel_hide() {
  datedit_hide_element(datedit_YEAR_SELECTOR);
}

/*----------------------------------------------------------------------------*/

function datedit_next_month() {
  if (datedit_MONTH<12) {
    datedit_show_date(datedit_DAY,datedit_MONTH+1,datedit_YEAR,datedit_HOUR,datedit_MINUTE,datedit_SECOND);
  } else {
    datedit_show_date(datedit_DAY,1,datedit_YEAR+1,datedit_HOUR,datedit_MINUTE,datedit_SECOND);
  }
  datedit_yearsel_hide();
  datedit_monthsel_hide();
  return false;
}

/*----------------------------------------------------------------------------*/

function datedit_prev_month() {
  if (datedit_MONTH>1) {
    datedit_show_date(datedit_DAY,datedit_MONTH-1,datedit_YEAR,datedit_HOUR,datedit_MINUTE,datedit_SECOND);
  } else {
    datedit_show_date(datedit_DAY,12,datedit_YEAR-1,datedit_HOUR,datedit_MINUTE,datedit_SECOND);
  }
  datedit_yearsel_hide();
  datedit_monthsel_hide();
  return false;
}

/*----------------------------------------------------------------------------*/

function datedit_change_year(year) {
  datedit_show_date(datedit_DAY,datedit_MONTH,year,datedit_HOUR,datedit_MINUTE,datedit_SECOND);
  datedit_yearsel_hide();
  datedit_monthsel_hide();
  return false;
}

/*----------------------------------------------------------------------------*/

function datedit_change_month(month) {
  datedit_show_date(datedit_DAY,month,datedit_YEAR,datedit_HOUR,datedit_MINUTE,datedit_SECOND);
  datedit_yearsel_hide();
  datedit_monthsel_hide();
  return false;
}

/*----------------------------------------------------------------------------*/

function datedit_month_length(month,year,offset) {
  monthLenArr =  new Array(31,28,31,30,31,30,31,31,30,31,30,31);
  if (offset != undefined) {
    month += offset;
    if (month>12) {year++;month=1;}
    if (month<1) {year--;month=12;}
  }
  var res = monthLenArr[month-1];
  if (month==2 && (year%4==0 && (year%100!=0 || year%400==0))) {
    res+=1;
  }
  return res;
}

/*----------------------------------------------------------------------------*/

function datedit_select_day(day) {
  datedit_show_date(day,datedit_MONTH,datedit_YEAR,datedit_HOUR,datedit_MINUTE,datedit_SECOND);
  datedit_ACTIVE_INPUT.value = datedit_format_output(datedit_ACTIVE_FORMAT);
  if (datedit_ACTIVE_INPUT.onchange) {
    var fce = datedit_ACTIVE_INPUT.onchange;
    fce();
  }
  if (!datedit_is_opera()) datedit_hide();
  return false;
}

function datedit_time_check() {
  var h = document.getElementById('datedit_hour');
  var m = document.getElementById('datedit_minute');
  var s = document.getElementById('datedit_second');
  var now = new Date();
  if (!h.value.match(/^[0-9]+$/)) h.value = now.getHours();
  if (!m.value.match(/^[0-9]+$/)) m.value = datedit_number_format(now.getMinutes(),2);
  if (!s.value.match(/^[0-9]+$/)) s.value = datedit_number_format(now.getSeconds(),2);
  datedit_HOUR = parseInt(h.value,10);
  datedit_MINUTE = parseInt(m.value,10);
  datedit_SECOND = parseInt(s.value,10);
  if (datedit_HOUR>23) {datedit_HOUR = 23;h.value=datedit_HOUR;}
  if (datedit_MINUTE>59) {datedit_MINUTE = 59;m.value=datedit_number_format(datedit_MINUTE,2);}
  if (datedit_SECOND>59) {datedit_SECOND = 59;s.value=datedit_number_format(datedit_SECOND,2);}
  datedit_ACTIVE_INPUT.value = datedit_format_output(datedit_ACTIVE_FORMAT);
}

/*----------------------------------------------------------------------------*/

function datedit_timebtn(diff,part) {
  var val = document.getElementById('datedit_'+part);
  var intval = 0;
  if (val.value.match(/^[0-9]+$/)) {
    intval = parseInt(val.value,10);
  }
  intval += diff;
  var out;
  if (part=='hour') {
    if (intval>=24) intval=0;
    if (intval<=-1) intval=23;
    out = intval;
    datedit_HOUR = intval;
  } else {
    if (intval>=60) intval=0;
    if (intval<=-1) intval=59;
    out = datedit_number_format(intval,2);
    if (part=='minute') {
      datedit_MINUTE = intval;
    } else {
      datedit_SECOND = intval;
    }    
  }
  datedit_ACTIVE_INPUT.value = datedit_format_output(datedit_ACTIVE_FORMAT);
  val.value = out;
}

/*----------------------------------------------------------------------------*/

function datedit_show_date(day,month,year,hour,minute,second) {
  datedit_DAY = day;
  datedit_MONTH = month;
  datedit_YEAR = year;
  datedit_HOUR = hour;
  datedit_MINUTE = minute;
  datedit_SECOND = second;
  var header = '<div class="top"><a href="#" class="navig prev" onclick="return datedit_prev_month();" title="'+datedit_PREV_MONTH+'"><span>«</span></a><a href="#" class="month" onclick="return datedit_monthsel_show(this);" title="'+datedit_CHANGE_MONTH+'">'+datedit_MONTH_NAMES[month-1]+'</a> <a href="#" class="year" onclick="return datedit_yearsel_show(this);" title="'+datedit_CHANGE_YEAR+'">'+year+'</a><a href="#" class="navig next" onclick="return datedit_next_month();" title="'+datedit_NEXT_MONTH+'"><span>»</span></a></div>';
  var content = '<div class="inner"><table style="border-collapse:separate;"><thead><tr>';
  for (i=0;i<7;i++) {
    // output week day names
    content += '<th>'+datedit_DAY_NAMES[(i+datedit_WEEK_STARTS)%7]+'</th>';
  }  
  content += '</tr></thead><tbody>';
  var date1st = new Date(datedit_YEAR,datedit_MONTH-1,1);
  var day1st = date1st.getDay(); 
  var dayIdx = datedit_WEEK_STARTS-day1st;
  if (dayIdx>0) dayIdx -= 7;
  var monLength = datedit_month_length(datedit_MONTH,datedit_YEAR);
  var prevMonLength = datedit_month_length(datedit_MONTH,datedit_YEAR,-1);
  
  var now = new Date();
  var today_day = now.getDate();
  var today_month = now.getMonth()+1;
  var today_year = now.getFullYear();
  while (dayIdx<monLength) {
    content += '<tr>';
    for (i=0;i<7;i++) {
      var dayOfWeek = (i+datedit_WEEK_STARTS)%7;
      dayIdx++;
      if (dayIdx<=0) {
        content += '<td class="inact">'+(prevMonLength+dayIdx)+'</td>';
      } else 
      if (dayIdx>monLength) {
        content += '<td class="inact">'+(dayIdx-monLength)+'</td>';
      } else {
        var sel = '';
        if (dayIdx==datedit_DAY_SEL && datedit_MONTH==datedit_MONTH_SEL && datedit_YEAR==datedit_YEAR_SEL) sel+=' sel'; 
        if (dayIdx==today_day && datedit_MONTH==today_month && datedit_YEAR==today_year) sel+=' today';
        if (dayOfWeek==6 || dayOfWeek==0) sel+=' weekend';
        if (sel.length>0) sel = ' class="'+sel.substring(1)+'"';
        content += '<td'+sel+'><a href="#" onclick="return datedit_select_day('+dayIdx+');">'+dayIdx+'</a></td>';
      }
    }
    content += '</tr>';
  }
   
  
  content += '</tbody></table></div>';
  var footer = '';
  if (hour>-1 || second>-1 || minute>-1) {
    footer += '<div class="time">';
    if (hour>-1) {
      footer += '<input type="text" size="2" value="'+hour+'" id="datedit_hour" onchange="datedit_time_check();" />';
      if (datedit_is_opera()) {
        footer += '<a class="btn1" onclick="datedit_timebtn(1,\'hour\');"><span>+</span></a><a class="btn2" onclick="datedit_timebtn(-1,\'hour\');"><span>-</span></a>';
      } else {
        footer += '<div><button class="btn1" onclick="datedit_timebtn(1,\'hour\');"><span>+</span></button><button class="btn2" onclick="datedit_timebtn(-1,\'hour\');"><span>-</span></button></div>';
      }
    }
    if (minute>-1)  {
      footer += ':<input type="text" size="2" value="'+datedit_number_format(minute,2)+'" id="datedit_minute" onchange="datedit_time_check();" />';
      if (datedit_is_opera()) {
        footer += '<a class="btn1" onclick="datedit_timebtn(1,\'minute\');"><span>+</span></a><a class="btn2" onclick="datedit_timebtn(-1,\'minute\');"><span>-</span></a>';
      } else {
        footer += '<div><button class="btn1" onclick="datedit_timebtn(1,\'minute\');"><span>+</span></button><button class="btn2" onclick="datedit_timebtn(-1,\'minute\');"><span>-</span></button></div>';
      }
    }
    if (second>-1) {
      footer += ':<input type="text" size="2" value="'+datedit_number_format(second,2)+'" id="datedit_second" onchange="datedit_time_check();" />';
      if (datedit_is_opera()) {
        footer += '<a class="btn1" onclick="datedit_timebtn(1,\'second\');"><span>+</span></a><a class="btn2" onclick="datedit_timebtn(-1,\'second\');"><span>-</span></a>';
      } else {
        footer += '<div><button class="btn1" onclick="datedit_timebtn(1,\'second\');"><span>+</span></button><button class="btn2" onclick="datedit_timebtn(-1,\'second\');"><span>-</span></button></div>';
      }
    }
    footer += '</div>';
  }
  datedit_DIV.innerHTML = header+content+footer;
  
  return false; 
}

/*----------------------------------------------------------------------------*/

function datedit_number_format(number,digits) {
  var res = number+"";
  while (res.length<digits) res = "0"+res;
  return res;
}

/*----------------------------------------------------------------------------*/

function get_position(element) {
  var x = 0;
  var y = 0;
	if (element.offsetParent) {
		do {
			x += element.offsetLeft;
			y += element.offsetTop;
		} while (element = element.offsetParent);
	}
	return new Array(x,y);
}

/*----------------------------------------------------------------------------*/

function datedit_format_output(format) {
  var res = format;
  res = res.replace("yyyy",datedit_number_format(datedit_YEAR,4));
  res = res.replace("mm",datedit_number_format(datedit_MONTH,2));
  res = res.replace("m",datedit_MONTH);
  res = res.replace("dd",datedit_number_format(datedit_DAY,2));
  res = res.replace("d",datedit_DAY);
  res = res.replace("HH",datedit_number_format(datedit_HOUR,2));
  res = res.replace("MM",datedit_number_format(datedit_MINUTE,2));
  res = res.replace("SS",datedit_number_format(datedit_SECOND,2));
  return res;  
}

/*----------------------------------------------------------------------------*/

function datedit_format2regexp(format) {
 var r = format; 
 var res = new Array();
 
 // get correct position D,M,Y
 res[1] = res[2] = res[3] = res[4] = res[5] = res[6] = 0;
 var fchars = Array("d","m","y","H","M","S");
 for (i=0;i<fchars.length;i++) {
   if (r.indexOf(fchars[i])<0) {
     res[i+1] = -1;
     continue;
   }
   for (j=0;j<fchars.length;j++) {
     if (i!=j) {
       if (r.indexOf(fchars[j])>=0 && r.indexOf(fchars[i])>r.indexOf(fchars[j])) res[i+1]++;
     }
   }
 }
 
 // convert format string to regexp
 r = r.replace(".","\\."); // escape dot
 r = r.replace(/y{4}/g,"([0-9]{4})");
 r = r.replace(/[dmHMS]{2}/g,"([0-9]{2})");
 r = r.replace(/[dm]{1}/g,"([0-9]{1,2})");
 res[0] = "^"+r+"$";
 return res;
}

/*----------------------------------------------------------------------------*/

function datedit_ANIMATE_FNS() {
  if (datedit_ANIMATION.length>0) {
    var ani = datedit_ANIMATION.shift();
    if (ani[1]!='hidden') {
      ani[0].style.visibility = 'visible';
      ani[0].style.height = ani[1];
    } else {
      ani[0].style.visibility = ani[1];
    }
    if (datedit_ANIMATION.length>0) setTimeout('datedit_ANIMATE_FNS();',datedit_ANIMATION_SPEED);
  }
}

/*----------------------------------------------------------------------------*/

function datedit_show_element(div) {
  if (div.style.visibility == 'visible') return;
  if (datedit_USE_ANIMATION) {
    div.style.height = 'auto';
    div.style.overflow = 'hidden';
    var height = div.offsetHeight;    
    for (i=1;i<=datedit_ANIMATION_STEPS-1;i++) {
      datedit_ANIMATION.push(new Array(div,(height/datedit_ANIMATION_STEPS*i)+'px'));
    }
    datedit_ANIMATION.push(new Array(div,'auto'));
    setTimeout('datedit_ANIMATE_FNS();',datedit_ANIMATION_SPEED);
  } else {
    div.style.visibility = 'visible';
  }
}

/*----------------------------------------------------------------------------*/

function datedit_hide_element(div) {
  if (div.style.visibility == 'hidden') return;
  if (datedit_USE_ANIMATION) {
    div.style.overflow = 'hidden';
    var height = div.offsetHeight;   
    for (i=datedit_ANIMATION_STEPS;i>0;i--) {
      datedit_ANIMATION.push(new Array(div,(height/datedit_ANIMATION_STEPS*i)+'px'));
    }
    datedit_ANIMATION.push(new Array(div,'hidden'));
    setTimeout('datedit_ANIMATE_FNS();',datedit_ANIMATION_SPEED);
  } else {
    div.style.visibility = 'hidden';
  }
}

/*----------------------------------------------------------------------------*/

function datedit_display(input,format) {
  
  datedit_ACTIVE_INPUT = input;
  datedit_ACTIVE_FORMAT = format;
  
  // hide month & year selector if visible
  datedit_monthsel_hide();
  datedit_yearsel_hide();
  
  // get position of input
	var pos = get_position(input);
	
  datedit_DIV.style.left = (pos[0]+(input.offsetWidth+datedit_BUTTON_WIDTH)/2-datedit_DIV.offsetWidth/2)+'px';
  datedit_DIV.style.top = (pos[1]+input.offsetHeight)+'px';

  // parse and set date
  var regexp_res = datedit_format2regexp(format);
  var re_string = regexp_res[0];
  var re = new RegExp(re_string);
  var now = new Date();
  var d = now.getDate();
  var m = now.getMonth()+1;
  var y = now.getFullYear();
  var HH = now.getHours();
  var MM = now.getMinutes();
  var SS = now.getSeconds();
  if (input.value!="") {
    if (input.value.match(re)) {
      // parse date
      var res = re.exec(input.value);
      d = parseInt(res[regexp_res[1]+1],10);
      m = parseInt(res[regexp_res[2]+1],10);
      y = parseInt(res[regexp_res[3]+1],10);
      // set time if format supports it
      if (regexp_res[4]>=0) HH = parseInt(res[regexp_res[4]+1],10);
      if (regexp_res[5]>=0) MM = parseInt(res[regexp_res[5]+1],10);
      if (regexp_res[6]>=0) SS = parseInt(res[regexp_res[6]+1],10);
      datedit_YEAR_SEL = y;
      datedit_MONTH_SEL = m;
      datedit_DAY_SEL = d;
    } else {
      datedit_YEAR_SEL = -1;
    }
  } 
  if (regexp_res[4]<0) HH = -1;
  if (regexp_res[5]<0) MM = -1;
  if (regexp_res[6]<0) SS = -1;
  
  // open calendar at specified date

  datedit_show_date(d,m,y,HH,MM,SS); 	
 	
	// show calendar DIV
  datedit_show_element(datedit_DIV);
   
}

/*----------------------------------------------------------------------------*/

function datedit_hide() {
  datedit_monthsel_hide();
  datedit_yearsel_hide();
  datedit_hide_element(datedit_DIV);
}

/*----------------------------------------------------------------------------*/

function datedit_input_valid(input,format) {
  var regexp_res = datedit_format2regexp(format);
  var re = new RegExp(regexp_res[0]);
  if (input.value!="") {
    if (!input.value.match(re)) return false;
    var res = re.exec(input.value);
    var d = parseInt(res[regexp_res[1]+1],10);
    var m = parseInt(res[regexp_res[2]+1],10);if (m>12 || m<1) return false;
    var y = parseInt(res[regexp_res[3]+1],10);
    var mlen = datedit_month_length(m,y);
    if (d>mlen || d<1) return false; 
    // set time if format supports it
    if (regexp_res[4]>=0) {var HH = parseInt(res[regexp_res[4]+1],10);if (HH>=24) return false;}
    if (regexp_res[5]>=0) {var MM = parseInt(res[regexp_res[5]+1],10);if (MM>=60) return false;}
    if (regexp_res[6]>=0) {var SS = parseInt(res[regexp_res[6]+1],10);if (SS>=60) return false;}
    
  }
  return true;
}

/*----------------------------------------------------------------------------*/


/* add event handler for input and preserve old ones */
function datedit_input_events(input,btn,format) {
  
 // set handler for button
  btn.onclick = function() {
    if (datedit_DIV.style.visibility != 'visible' || input != datedit_ACTIVE_INPUT) {
      datedit_display(input,format);
    } else {
      datedit_hide();
    }
  }   
    
}

/*----------------------------------------------------------------------------*/

function datedit_add_onsubmit(func,form) {
  var old_onsubmit =  form.onsubmit;
  if (typeof form.onsubmit != 'function') {
    form.onsubmit = func;
  } else {
    form.onsubmit = function() {
      if (!old_onsubmit()) return false;
      return func();
    }
  }
}

/*----------------------------------------------------------------------------*/

function datedit_load_input(input,format) {
  var regexp_res = datedit_format2regexp(format);
  var re = new RegExp(regexp_res[0]);
  var now = new Date();
  datedit_DAY = now.getDate();
  datedit_MONTH = now.getMonth()+1;
  datedit_YEAR = now.getFullYear();  
  datedit_HOUR = now.getHours();
  datedit_MINUTE = now.getMinutes();
  datedit_SECOND = now.getSeconds();
  if (input.value.match(re) && input.value.length>0) {
    // parse date
    var res = re.exec(input.value);
    if (regexp_res[1]>=0) datedit_DAY = parseInt(res[regexp_res[1]+1],10);
    if (regexp_res[2]>=0) datedit_MONTH = parseInt(res[regexp_res[2]+1],10); 
    if (regexp_res[3]>=0) datedit_YEAR = parseInt(res[regexp_res[3]+1],10);
    // set time if format supports it
    if (regexp_res[4]>=0) datedit_HOUR = parseInt(res[regexp_res[4]+1],10);
    if (regexp_res[5]>=0) datedit_MINUTE = parseInt(res[regexp_res[5]+1],10); 
    if (regexp_res[6]>=0) datedit_SECOND = parseInt(res[regexp_res[6]+1],10);
  }
}

/*----------------------------------------------------------------------------*/

/* function providing proper loading of datedit elements */
function datedit_init() {
  
  // create buttons
  for(idx=0;idx<datedit_ELEMENTS.length;idx++) { // apply to all element needed
    var element = datedit_ELEMENTS[idx];
    var input = document.getElementById(element[0]);
    var btn = document.createElement('button');
    var btnText = document.createElement('span');
    btnText.appendChild(document.createTextNode(datedit_BUTTON_TEXT));
    btn.appendChild(btnText);
    btn.setAttribute('type','button');
    btn.setAttribute('title',datedit_BUTTON_HINT);
    btn.className = 'datedit';
    datedit_input_events(input,btn,element[1]); // add event handlers
    input.parentNode.insertBefore(btn,input.nextSibling);
    
    // set readonly element if needed
    if (!element[2]) {
      input.readOnly = true;
    }
    
    // onload convert fields
    if (element[3]!=null) {
      datedit_load_input(input,element[3]);
      input.value = datedit_format_output(element[1]);
    }     
    
  }
  
  var check_fce = function() {
    for(idx2=0;idx2<datedit_ELEMENTS.length;idx2++) { // apply to all element needed
      var element2 = datedit_ELEMENTS[idx2];
      var input2 = document.getElementById(element2[0]);
      if (datedit_VALIDATE_OUTPUT) {
        if (!datedit_input_valid(input2,element2[1])) {
          window.alert(datedit_INVALID_DATE_FORMAT+"\n"+input2.value);
          input2.focus();
          return false;
        }  
        if (element2[3]!=null) {
          datedit_load_input(input2,element2[1]);
          input2.value = datedit_format_output(element2[3]);
        }
      }
    }
    return true;
  }
  datedit_add_onsubmit(check_fce,input.form);  
  
  // create datedit root
  datedit_ROOT = document.createElement('div');
  document.body.appendChild(datedit_ROOT);
  
  // create calendar area
  datedit_DIV = document.createElement('div');
  datedit_DIV.className = 'datedit';
  datedit_DIV.style.visibility = 'hidden';
  datedit_DIV.style.position = 'absolute';
  datedit_ROOT.appendChild(datedit_DIV);
  
  // create month selector
  datedit_MONTH_SELECTOR = document.createElement('ul');
  datedit_MONTH_SELECTOR.className='dateditMonthSelector dateditSelector';
  datedit_MONTH_SELECTOR.style.position = 'absolute';
  datedit_MONTH_SELECTOR.style.visibility = 'hidden';
  datedit_MONTH_SELECTOR.style.top = '0px';
  var li_head = document.createElement('li');
  li_head.className = 'head';
  li_head.innerHTML = datedit_MONTH_HEAD;
  datedit_MONTH_SELECTOR.appendChild(li_head);
  for (i=0;i<datedit_MONTH_NAMES.length;i++) {
    var li = document.createElement('li');
    li.innerHTML = '<a href="#" onclick="return datedit_change_month('+(i+1)+');">'+datedit_MONTH_NAMES[i]+'</a>';    
    datedit_MONTH_SELECTOR.appendChild(li);
  }
  datedit_ROOT.appendChild(datedit_MONTH_SELECTOR);
  
  // create year selector
  datedit_YEAR_SELECTOR = document.createElement('ul');
  datedit_YEAR_SELECTOR.className='dateditSelector';
  datedit_YEAR_SELECTOR.style.position = 'absolute';
  datedit_YEAR_SELECTOR.style.visibility = 'hidden';
  datedit_YEAR_SELECTOR.style.top = '0px';  
  document.body.appendChild(datedit_YEAR_SELECTOR);
  var li_head = document.createElement('li');
  li_head.className = 'head';
  li_head.innerHTML = datedit_YEAR_HEAD;
  datedit_YEAR_SELECTOR.appendChild(li_head);
  for (i=0;i<datedit_YEAR_DIFF.length;i++) {
    var li = document.createElement('li');
    datedit_YEAR_SELECTOR.appendChild(li);
  }
  datedit_ROOT.appendChild(datedit_YEAR_SELECTOR);
  
}

/*----------------------------------------------------------------------------*/

/* add datedit load function to body */
var old_onload = window.onload;

if (typeof window.onload != 'function'){ 
 	window.onload = datedit_init; // document hasn't defined onload function, overwrite
} else {  
  window.onload = function() { // document has defined onload function preserve and add datedit load func
    old_onload();
	  datedit_init();
  }
}

document.onclick = global_click;
