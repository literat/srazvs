/**
 * Generate a new password, which may then be copied to the form
 * with suggestPasswordCopy().
 *
 * @param   string   the form name
 *
 * @return  boolean  always true
 */
function suggestPassword() {
    // restrict the password to just letters and numbers to avoid problems:
    // "editors and viewers regard the password as multiple words and
    // things like double click no longer work"
    var pwchars = "abcdefhjmnpqrstuvwxyz23456789ABCDEFGHJKLMNPQRSTUVWYXZ";
    var passwordlength = 16;    // do we want that to be dynamic?  no, keep it simple :)
    var passwd = document.getElementById('generated_pw');
    passwd.value = '';

    for ( i = 0; i < passwordlength; i++ ) {
        passwd.value += pwchars.charAt( Math.floor( Math.random() * pwchars.length ) )
    }
    return passwd.value;
}


/**
 * Copy the generated password (or anything in the field) to the form
 *
 * @param   string   the form name
 *
 * @return  boolean  always true
 */
function suggestPasswordCopy() {
    document.getElementById('text_pma_pw').value = document.getElementById('generated_pw').value;
    document.getElementById('text_pma_pw2').value = document.getElementById('generated_pw').value;
    return true;
}

/**
 * Opens calendar window.
 *
 * @param   string      calendar.php parameters
 * @param   string      form name
 * @param   string      field name
 * @param   string      edit type - date/timestamp
 */
function openCalendar(params, form, field, type) {
    window.open("../calendar/mini_calendar.php?" + params, "calendar", "width=400,height=200,status=yes");
    dateField = eval("document." + form + "." + field);
    dateType = type;
}

/**
 * Formats number to two digits.
 *
 * @param   int number to format.
 * @param   string type of number
 */
function formatNum2(i, valtype) {
    f = (i < 10 ? '0' : '') + i;
    if (valtype && valtype != '') {
        switch(valtype) {
            case 'month':
                f = (f > 12 ? 12 : f);
                break;

            case 'day':
                f = (f > 31 ? 31 : f);
                break;

            case 'hour':
                f = (f > 24 ? 24 : f);
                break;

            default:
            case 'second':
            case 'minute':
                f = (f > 59 ? 59 : f);
                break;
        }
    }

    return f;
}

/**
 * Formats number to two digits.
 *
 * @param   int number to format.
 * @param   int default value
 * @param   string type of number
 */
function formatNum2d(i, default_v, valtype) {
    i = parseInt(i, 10);
    if (isNaN(i)) return default_v;
    return formatNum2(i, valtype)
}

/**
 * Formats number to four digits.
 *
 * @param   int number to format.
 */
function formatNum4(i) {
    i = parseInt(i, 10)
    return (i < 1000 ? i < 100 ? i < 10 ? '000' : '00' : '0' : '') + i;
}

/**
 * Initializes calendar window.
 */
function initCalendar() {
    if (!year && !month && !day) {
        /* Called for first time */
        if (window.opener.dateField.value) {
            value = window.opener.dateField.value;
            if (window.opener.dateType == 'datetime' || window.opener.dateType == 'date') {
                if (window.opener.dateType == 'datetime') {
                    parts   = value.split(' ');
                    value   = parts[0];

                    if (parts[1]) {
                        time    = parts[1].split(':');
                        hour    = parseInt(time[0],10);
                        minute  = parseInt(time[1],10);
                        second  = parseInt(time[2],10);
                    }
                }
                date        = value.split("-");
                day         = parseInt(date[2],10);
                month       = parseInt(date[1],10) - 1;
                year        = parseInt(date[0],10);
            } else {
                year        = parseInt(value.substr(0,4),10);
                month       = parseInt(value.substr(4,2),10) - 1;
                day         = parseInt(value.substr(6,2),10);
                hour        = parseInt(value.substr(8,2),10);
                minute      = parseInt(value.substr(10,2),10);
                second      = parseInt(value.substr(12,2),10);
            }
        }
        if (isNaN(year) || isNaN(month) || isNaN(day) || day == 0) {
            dt      = new Date();
            year    = dt.getFullYear();
            month   = dt.getMonth();
            day     = dt.getDate();
        }
        if (isNaN(hour) || isNaN(minute) || isNaN(second)) {
            dt      = new Date();
            hour    = dt.getHours();
            minute  = dt.getMinutes();
            second  = dt.getSeconds();
        }
    } else {
        /* Moving in calendar */
        if (month > 11) {
            month = 0;
            year++;
        }
        if (month < 0) {
            month = 11;
            year--;
        }
    }

    if (document.getElementById) {
        cnt = document.getElementById("calendar_data");
    } else if (document.all) {
        cnt = document.all["calendar_data"];
    }

    cnt.innerHTML = "";

    str = ""

    //heading table
    str += '<table class="calendar"><tr><th width="50%">';
    str += '<form method="NONE" onsubmit="return 0">';
    str += '<a href="javascript:month--; initCalendar();">&laquo;</a> ';
    str += '<select id="select_month" name="monthsel" onchange="month = parseInt(document.getElementById(\'select_month\').value); initCalendar();">';
    for (i =0; i < 12; i++) {
        if (i == month) selected = ' selected="selected"';
        else selected = '';
        str += '<option value="' + i + '" ' + selected + '>' + month_names[i] + '</option>';
    }
    str += '</select>';
    str += ' <a href="javascript:month++; initCalendar();">&raquo;</a>';
    str += '</form>';
    str += '</th><th width="50%">';
    str += '<form method="NONE" onsubmit="return 0">';
    str += '<a href="javascript:year--; initCalendar();">&laquo;</a> ';
    str += '<select id="select_year" name="yearsel" onchange="year = parseInt(document.getElementById(\'select_year\').value); initCalendar();">';
    for (i = year - 25; i < year + 25; i++) {
        if (i == year) selected = ' selected="selected"';
        else selected = '';
        str += '<option value="' + i + '" ' + selected + '>' + i + '</option>';
    }
    str += '</select>';
    str += ' <a href="javascript:year++; initCalendar();">&raquo;</a>';
    str += '</form>';
    str += '</th></tr></table>';

    str += '<table class="calendar"><tr>';
    for (i = 0; i < 7; i++) {
        str += "<th>" + day_names[i] + "</th>";
    }
    str += "</tr>";

    var firstDay = new Date(year, month, 1).getDay();
    var lastDay = new Date(year, month + 1, 0).getDate();

    str += "<tr>";

    dayInWeek = 0;
    for (i = 0; i < firstDay; i++) {
        str += "<td>&nbsp;</td>";
        dayInWeek++;
    }
    for (i = 1; i <= lastDay; i++) {
        if (dayInWeek == 7) {
            str += "</tr><tr>";
            dayInWeek = 0;
        }

        dispmonth = 1 + month;

        if (window.opener.dateType == 'datetime' || window.opener.dateType == 'date') {
            actVal = "" + formatNum4(year) + "-" + formatNum2(dispmonth, 'month') + "-" + formatNum2(i, 'day');
        } else {
            actVal = "" + formatNum4(year) + formatNum2(dispmonth, 'month') + formatNum2(i, 'day');
        }
        if (i == day) {
            style = ' class="selected"';
            current_date = actVal;
        } else {
            style = '';
        }
        str += "<td" + style + "><a href=\"javascript:returnDate('" + actVal + "');\">" + i + "</a></td>"
        dayInWeek++;
    }
    for (i = dayInWeek; i < 7; i++) {
        str += "<td>&nbsp;</td>";
    }

    str += "</tr></table>";

    cnt.innerHTML = str;

    // Should we handle time also?
    if (window.opener.dateType != 'date' && !clock_set) {

        if (document.getElementById) {
            cnt = document.getElementById("clock_data");
        } else if (document.all) {
            cnt = document.all["clock_data"];
        }

        str = '';
        init_hour = hour;
        init_minute = minute;
        init_second = second;
        str += '<fieldset>';
        str += '<form method="NONE" class="clock" onsubmit="returnDate(\'' + current_date + '\')">';
        str += '<input id="hour"    type="text" size="2" maxlength="2" onblur="this.value=formatNum2d(this.value, init_hour, \'hour\'); init_hour = this.value;" value="' + formatNum2(hour, 'hour') + '" />:';
        str += '<input id="minute"  type="text" size="2" maxlength="2" onblur="this.value=formatNum2d(this.value, init_minute, \'minute\'); init_minute = this.value;" value="' + formatNum2(minute, 'minute') + '" />:';
        str += '<input id="second"  type="text" size="2" maxlength="2" onblur="this.value=formatNum2d(this.value, init_second, \'second\'); init_second = this.value;" value="' + formatNum2(second, 'second') + '" />';
        str += '&nbsp;&nbsp;';
        str += '<input type="submit" value="' + submit_text + '"/>';
        str += '</form>';
        str += '</fieldset>';

        cnt.innerHTML = str;
        clock_set = 1;
    }

}

/**
 * Returns date from calendar.
 *
 * @param   string     date text
 */
function returnDate(d) {
    txt = d;
    if (window.opener.dateType != 'date') {
        // need to get time
        h = parseInt(document.getElementById('hour').value,10);
        m = parseInt(document.getElementById('minute').value,10);
        s = parseInt(document.getElementById('second').value,10);
        if (window.opener.dateType == 'datetime') {
            txt += ' ' + formatNum2(h, 'hour') + ':' + formatNum2(m, 'minute') + ':' + formatNum2(s, 'second');
        } else {
            // timestamp
            txt += formatNum2(h, 'hour') + formatNum2(m, 'minute') + formatNum2(s, 'second');
        }
    }

    window.opener.dateField.value = txt;
    window.close();
}



//-------------------------------------------------
function getE(id)
{
   if (typeof id != "string")
   {
         return id;
   }
   else if (Boolean(document.getElementById))
   {
         return document.getElementById(id);
   }
   else if (Boolean(document.all))
   {
         return eval("document.all."+id);
   }
   else if (Boolean(document.ids))
   {
         return eval("document.ids."+id);
   }
   else
   {
         return null;
   }
}
//-------------------------------------------------
function okno(action,winwidth,winheight,name,scroll,toolbar,status,resizable,menubar) {
	var PROFILE = null;
       
	PROFILE =  window.open ("", name, "toolbar="+toolbar+",width="+winwidth+",height="+winheight+",directories=no,location=no,status="+status+",scrollbars="+scroll+",resizable="+resizable+",menubar="+menubar+"");
        if (PROFILE != null) {
               if (PROFILE.opener == null) {
                   PROFILE.opener = self;
        	   }
	       PROFILE.location.href = action;
        } 
       
       
}
//-------------------------------------------------
function reokno(action,winwidth,winheight,name,scroll,toolbar,status,resize,menubar) 
	{
	
	window.close();
	
	b = window.opener;
	a = b.closed;
				
	if (!a)
	{
		var PROFILE = null;
	       	PROFILE =  self.open ("", "nalezen", "toolbar="+toolbar+",width="+winwidth+",height="+winheight+",directories=no, status="+status+",scrollbars="+scroll+",resize="+resize+",menubar="+menubar+"");
	        if (PROFILE != null) {
	               if (PROFILE.opener == null) {
	                   PROFILE.opener = self;
	        	   }
		       PROFILE.location.href = action;
	}
        }
}
//-------------------------------------------------
function reoknoclen(action,winwidth,winheight,name,scroll,toolbar,status,resize,menubar) 
	{
	
	window.close();
	
	b = window.opener;
	a = b.closed;
				
	if (!a)
	{
		var PROFILE = null;
	       	PROFILE =  self.open ("", "clen", "toolbar="+toolbar+",width="+winwidth+",height="+winheight+",directories=no, status="+status+",scrollbars="+scroll+",resize="+resize+",menubar="+menubar+"");
	        if (PROFILE != null) {
	               if (PROFILE.opener == null) {
	                   PROFILE.opener = self;
	        	   }
		       PROFILE.location.href = action;
	}
        }
        }
//-------------------------------------------------
function GetElementById(id){
	if (document.getElementById) {
		return (document.getElementById(id));
	} else if (document.all) {
		return (document.all[id]);
	} else {
		if ((navigator.appname.indexOf("Netscape") != -1) && parseInt(navigator.appversion == 4)) {
			return (document.layers[id]);
		}
	}
}
//-------------------------------------------------
function CheckAll(formname, switchid) {
	var ele = document.forms[formname].elements;
	var switch_cbox = GetElementById(switchid);
	for (var i = 0; i < ele.length; i++) {
		var e = ele[i];
		if ( (e.name != switch_cbox.name) && (e.type == 'checkbox') ) {
			e.checked = switch_cbox.checked;
		}
	}
}
//-------------------------------------------------
function stylTextu(formname, policko, styl) {
if(styl == "a")
{
	var adresa = prompt('Zadej adresu odkazu', '');
	var text = prompt('Zadej název odkazu', '');
	document.forms[formname].elements[policko].value += "<"+ styl +" href=\""+ adresa +"\">"+ text +"</"+ styl +">";
}
else
{
	var text = prompt('Zadej text', '');
	document.forms[formname].elements[policko].value += "<"+ styl +">"+ text +"</"+ styl +">";
}
}
//-------------------------------------------------
function anketa_check_form(formname,moznost) {
	var ele = formname.moznost;
var chyba = "Nevybrali jste žádnou možnost!";
		for (var i = 0; i < ele.length; i++) {
			var e = ele[i];
			if (e.checked == true){
			var chyba = "";
			}
		}
		if (chyba != ""){
			alert (chyba);
			return false;
		}
		else return true;
}
//-------------------------------------------------
function confirmation(adresa, hlaska) 
          {
          var potvrdit = confirm (hlaska);
          if (potvrdit)
               {
               //self.location.href = adresa;
			   window.location.replace(adresa);
			   }
	     }
//-------------------------------------------------
function validate_email(field, alerttxt)
		{
			with (field){
				apos=value.indexOf("@");
				dotpos=value.lastIndexOf(".");
				if (apos<1||dotpos-apos<2){
					alert(alerttxt);
					return false;
				}
				else {return true;}
			}
		}
//-------------------------------------------------
function validate_empty(field, alerttxt)
		{
			with (field){
				if (value == ""){
					alert(alerttxt);
					return false;
				}
				else {return true;}
			}
		}
//-------------------------------------------------
function UkazHide(div,stav)
{
   var d = getE(div);
   var _stav = stav;

   if (d == null) return;

   if (stav == 3)
   {
         if (d.style.display == "") _stav = 0;
         else                       _stav = 1;
   }

   if (_stav == 0)
   {
         d.style.visibility = "hidden";
         d.style.display    = "none";
   }
   else
   {
         d.style.visibility = "visible";
         d.style.display    = "";
   }
}
//-------------------------------------------------
function ZmenSelect(ele,value)
{
   var d = getE(ele);
   var v = value;
   d.selectedIndex = v;
}
/*
function checkAllCheckboxes(field)
{
	for (i = 0; i < field.length; i++)
		field[i].checked = true ;
}

function uncheckAllCheckboxes(field)
{
	for (i = 0; i < field.length; i++)
		field[i].checked = false ;
}

function checkAll(theForm)
{
	for (i=0;i<theForm.elements.length;i++)
		if (theForm.elements[i].name.indexOf('checker') !=-1)
		theForm.elements[i].checked = true;
}

function uncheckAll(theForm)
{
	for (i=0;i<theForm.elements.length;i++)
		if (theForm.elements[i].name.indexOf('checker') !=-1)
		theForm.elements[i].checked = false;
}*/

function select(boolean) {
	var theForm = document.checkerForm;
	for (i=0; i<theForm.elements.length; i++) {
		if (theForm.elements[i].name=='checker[]')
		theForm.elements[i].checked = boolean;
	}
}