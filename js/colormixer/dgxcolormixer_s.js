//
// DGX Color Mixer 
// ---------------
//
// The source code is protected by copyright laws and international
// copyright treaties, as well as other intellectual property laws
// and treaties. Licensed under GPL.
//
// author: David Grudl <david@grudl.com> http://www.davidgrudl.com
// version: 1.0
//



// ------------------------------


// elements real position
function getOffset(element) 
{  
    var x=0, y=0;  
    while (element) {
        x += element.offsetLeft - element.scrollLeft;
        y += element.offsetTop - element.scrollTop;
        element = element.offsetParent;
    }
    
    var result = new Object(); result.x = x; result.y = y;
    return result;  
}



// set object's opacity in range 0..100
function setAlpha(obj, alpha) 
{
   obj.style.opacity = alpha / 100;
   if (obj.filters && obj.filters.alpha)
        obj.filters.alpha.opacity = alpha;
}


function createHandler(obj, handler) 
{
    return function(e) { obj[handler](e); }
}



function AddBodyEvent(eventName, obj, handler) { 
    if (document.body.addEventListener) { 
        document.body.addEventListener(eventName, function(e) { obj[handler](e); }, false);
        return;
    }
/*
    if (document.body.attachEvent) {      
        document.body.attachEvent('on' + eventName, function(e) { obj[handler](e); });
        return;
    }
*/
    var originalHandler = document.body['on' + eventName]; 
    if (originalHandler) 
        document.body['on' + eventName] = function(e) { originalHandler(e); obj[handler](e); } 
    else 
        document.body['on' + eventName] = function(e) { obj[handler](e); } 
}



// ------------------------------




// color in RGB color space (see http://en.wikipedia.org/wiki/RGB_color_space)
// params red, green, blue, in the range 0..255
function RGBColor(r, g, b) 
{
    this.r = Math.min(Math.max(r, 0), 255);
    this.g = Math.min(Math.max(g, 0), 255);
    this.b = Math.min(Math.max(b, 0), 255);
}

RGBColor.prototype.r = 0;
RGBColor.prototype.g = 0;
RGBColor.prototype.b = 0;


RGBColor.prototype.toHSV = function ()
{
    r = this.r / 255;
    g = this.g / 255;
    b = this.b / 255;
    
    v = Math.max(Math.max(r,g),b);
    t = Math.min(Math.min(r,g),b);
    s = (v==0) ? 0 : (v-t)/v;
    if (s==0) h=0;
    else {
        a = v-t;
        cr = (v-r)/a;
        cg = (v-g)/a;
        cb = (v-b)/a;
        h = (r==v) ? cb-cg : ((g==v) ? 2+cr-cb : ((b==v) ? h=4+cg-cr : 0));
        h = 60 * h;
        if (h<0) h+=360;
    }
 
    return new HSVColor(h, s*100, v*100);
}


RGBColor.prototype.toRGB = function ()
{
    return this;
}


RGBColor.prototype.toHEX = function () 
{
    var hexenschuss="0123456789ABCDEF";
    return hexenschuss.charAt(this.r >> 4) + hexenschuss.charAt(this.r & 15) +
           hexenschuss.charAt(this.g >> 4) + hexenschuss.charAt(this.g & 15) +
           hexenschuss.charAt(this.b >> 4) + hexenschuss.charAt(this.b & 15);
}


RGBColor.prototype.brightness = function ()
{
    return 0.2125 * this.r + 0.7154 * this.g + 0.0721 * this.b;
}




// ------------------------------


// color in HSV (HSB) color space (see http://en.wikipedia.org/wiki/HSV_color_space)
// param h hue, in the range 0..359
// param s saturation, in the range 0..100
// param v brightness, in the range 0..100
function HSVColor(h, s, v) 
{
    this.h = (h % 360 + 360) % 360;
    this.s = Math.min(Math.max(s, 0), 100);
    this.v = Math.min(Math.max(v, 0), 100);
}

HSVColor.prototype.h = 0;
HSVColor.prototype.s = 0;
HSVColor.prototype.v = 0;


HSVColor.prototype.toHSV = function ()
{
    return this;
}


HSVColor.prototype.toRGB = function ()
{
    var h = (this.h % 360) / 360;
    var s = this.s / 100;
    var v = this.v / 100;

    var r, g, b;
    var hue = h*6;
    var i = Math.floor(hue);
    var f = hue - i;
    var w = v * (1 - s);
    var q = v * (1 - (s * f));
    var t = v * (1 - (s * (1 - f)));
    switch(i) {
    case 0: r = v; g = t; b = w; break;
    case 1: r = q; g = v; b = w; break;
    case 2: r = w; g = v; b = t; break;
    case 3: r = w; g = q; b = v; break;
    case 4: r = t; g = w; b = v; break;
    case 5: r = v; g = w; b = q; break;
    }

    return new RGBColor(Math.round(r*255), Math.round(g*255), Math.round(b*255));  
}




// ------------------------------


function DGXColorMixer(prefix) 
{
    this.color = new HSVColor(0, 100, 100);
    this.onChange = null;
    this.onConfirm = null;
    this.isDragged = false;
    this.attachedInput = null;
    this.isPopup = false;
    this.isVisible = false;
    this.elements = {};

    function createElement(name, parent, tag) {
        var obj = document.createElement(tag ? tag : 'div');
        obj.className = 'DGXColorMixer_' + name;
        if (prefix) obj.id = prefix + name;
        parent.appendChild(obj);
        return obj;
    }
                   
    this.elements.main      = createElement('main', document.body);    
    this.elements.box       = createElement('box', this.elements.main);    
    this.elements.crosshair = createElement('crosshair', this.elements.box);    
    this.elements.gradient  = createElement('gradient', this.elements.main);    
    this.elements.slider    = createElement('slider', this.elements.gradient);    
    this.elements.color     = createElement('color', this.elements.main);    
    this.elements.okbutton  = createElement('okbutton', this.elements.color, 'button');    
    this.elements.okbutton.innerHTML = '&nbsp;ok&nbsp;';

    this.elements.box.onmousedown = 
    this.elements.crosshair.onmousedown = createHandler(this, '_beginDragBox');
    this.elements.gradient.onmousedown = 
    this.elements.slider.onmousedown = createHandler(this, '_beginDragGradient');
    this.elements.box.ondblclick = 
    this.elements.crosshair.ondblclick = 
    this.elements.color.ondblclick =
    this.elements.okbutton.onclick = createHandler(this, '_confirm');

    AddBodyEvent('mouseup', this, '_endDrag');
    AddBodyEvent('mousedown', this, '_endPopup');
    AddBodyEvent('mousemove', this, '_drag');
    if (document.all) AddBodyEvent('selectstart', this, '_drag');

    this.redraw();
}



DGXColorMixer.prototype.setColor = function (color) 
{
    if (typeof color == 'string') {
        var hex = color.match(/^#?([0-9a-zA-Z]{3})\s*$/);
        if (hex) color = new RGBColor(parseInt(hex[0].charAt(0), 16) * 17, parseInt(hex[0].charAt(1), 16) * 17, parseInt(hex[0].charAt(2), 16) * 17);           
        else {
            hex = color.match(/^#?([0-9a-zA-Z]{6})\s*$/);
            if (hex) color = new RGBColor(parseInt(hex[0].substr(0, 2), 16), parseInt(hex[0].substr(2, 2), 16), parseInt(hex[0].substr(4, 2), 16));           
        }
    }
    
    if (color.toHSV) 
        this.color = color.toHSV();
    else 
        return false;
    
    this.redraw();
    
    return true; 
}


DGXColorMixer.prototype.setParent = function (element) 
{
    if ((typeof element) == 'string') 
        element = document.getElementById(element);

    if (!element) return false;

    return element.appendChild(this.elements.main);
}


DGXColorMixer.prototype.attachInput = function (element, asPopup) 
{
    if ((typeof element) == 'string') 
        element = document.getElementById(element);

    if (!element) return false;

    this.attachedInput = element;
    this.setColor(this.attachedInput.value);
    
    this.isPopup = asPopup;
    this.elements.okbutton.style.display = this.isPopup ? 'inline' : 'none';
    if (this.isPopup)
        this.attachedInput.ondblclick = createHandler(this, 'popup');
}


DGXColorMixer.prototype.hide = function () 
{
    this.isDragged = false;
    this.elements.main.style.display = 'none';
    this.isVisible = false;
    return true;
}


DGXColorMixer.prototype.show = function () 
{
    this.elements.main.style.display = 'block';
    this.isVisible = true;
    return true;
}



DGXColorMixer.prototype.popup = function () 
{             
    if (!this.isPopup || !this.attachedInput) return false;

    this.setColor(this.attachedInput.value);
        
    offset = getOffset(this.attachedInput);
    offset.x += this.attachedInput.offsetWidth;
    this.elements.main.style.left = offset.x + 'px';
    this.elements.main.style.top = offset.y + 'px';
    this.elements.main.style.position = 'absolute';        
    
    this.show();

    return true;
}



DGXColorMixer.prototype.redraw = function ()
{
    // box
    this.elements.box.style.backgroundColor = '#' + (new HSVColor(this.color.h, 100, 100)).toRGB().toHEX();

    // color
    this.elements.color.style.backgroundColor = '#'+this.color.toRGB().toHEX();

    // slider
    this.elements.slider.style.top = ((359 - this.color.h) * 316 / 359 - 2) + 'px';

    // crosshair
    this.elements.crosshair.style.left = (this.color.s / 100 * 255 - 6) + 'px';
    this.elements.crosshair.style.top = ((100-this.color.v) / 100 * 255 - 6) + 'px';

    // user handler
    if (this.onChange) this.onChange(this);

    return true;
}




DGXColorMixer.prototype._confirm = function (e) 
{
    this.isDragged = false;
    
    if (this.attachedInput) 
        this.attachedInput.value = this.color.toRGB().toHEX();
        
    if (this.isPopup) this.hide();

    if (this.onConfirm) this.onConfirm(this);    
}


DGXColorMixer.prototype._beginDragGradient = function (e) 
{
    this.isDragged = 'gradient';
    this._drag(e);
}


DGXColorMixer.prototype._beginDragBox = function (e) 
{
    this.isDragged = 'box';
    this._drag(e);
}


DGXColorMixer.prototype._endDrag = function (e) 
{
    this.isDragged = false;
}


DGXColorMixer.prototype._endPopup = function (e) 
{             
    if (!this.isPopup || !this.isVisible) return;

    e = e||window.event;
    var eX = e.pageX||e.clientX;
    var eY = e.pageY||e.clientY;

    var offset = getOffset(this.elements.main);
    if ((eX < offset.x) || (eX > offset.x + this.elements.main.offsetWidth) || 
        (eY < offset.y) || (eY > offset.y + this.elements.main.offsetHeight)) { 
        this.hide();
        if (event.cancelBubble) event.cancelBubble = true;
        if (event.stopPropagation) event.stopPropagation();
        if (event.preventDefault) event.preventDefault();
    }
}



DGXColorMixer.prototype._drag = function (e)
{
    if (!this.isDragged) return;

    e = e||window.event;
    var eX = e.pageX||e.clientX;
    var eY = e.pageY||e.clientY;

    var x,y;

    if (this.isDragged=='gradient') 
    {
        var offset = getOffset(this.elements.gradient);
        x = (eY - offset.y - 1) / 316 * 359;
        x = 359 - x;
        x = Math.min(Math.max(x, 0), 359);
        
        this.color.h = x;
    }

    if (this.isDragged=='box') 
    {
        var offset = getOffset(this.elements.box);
        x = (eX - offset.x - 1) / 255 * 100;
        y = 100 - (eY - offset.y - 1) / 255 * 100;
        x = Math.min(Math.max(x, 0), 100);
        y = Math.min(Math.max(y, 0), 100);
        
        this.color.s = x;
        this.color.v = y;
    }

    this.redraw();
    return true;
}


