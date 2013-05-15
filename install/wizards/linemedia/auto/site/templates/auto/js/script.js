$(document).ready(function(){
	

});

/* добавление в закладки */
function getBrowserInfo() {
 var t,v = undefined;
 if (window.opera) t = 'Opera';
 else if (document.all) {
  t = 'IE';
  var nv = navigator.appVersion;
  var s = nv.indexOf('MSIE')+5;
  v = nv.substring(s,s+1);
 }
 else if (navigator.appName) t = 'Netscape';
 return {type:t,version:v};
}

function bookmark(a){
 var url = window.document.location;
 var title = window.document.title;
 var b = getBrowserInfo();
 if (b.type == 'IE' && 7 > b.version && b.version >= 4) window.external.AddFavorite(url,title);
 else if (b.type == 'Opera') {
  a.href = url;
  a.rel = "sidebar";
  a.title = url+','+title;
  return true;
 }
 else if (b.type == "Netscape") window.sidebar.addPanel(title,url,"");
 else alert("Нажмите CTRL-D, чтобы добавить страницу в закладки.");
 return false;
}

/* cart quantity ops */
function get_value(el) {
  if (el) {
    var x = parseInt(el.value);
    return isNaN(x) ? 0 : x;
  } else return 0;
}

function inc_value(el) {
  do { el = el.previousSibling; } while (el.tagName != 'INPUT');
  var x = get_value(el);
  el.value = x + 1 + "";
}

function dec_value(el) {
  do { el = el.nextSibling; } while (el.tagName != 'INPUT');
  var x = get_value(el);
  el.value = x > 1 ? x - 1 + "" : "1";
}

function validate_int(el, min, max) {
  var x = '';
  var i;
  for (i = 0; i < el.value.length; i++) {
    var c = parseInt(el.value.charAt(i));
    if (0 <= c && 9 >= c) x += (c + '');
  }
  if (null != max && parseInt(x) > max) el.value = max + '';
  else if (null != min && parseInt(x) < min) el.value = min + '';
  else if (el.value != x) el.value = x;
}
