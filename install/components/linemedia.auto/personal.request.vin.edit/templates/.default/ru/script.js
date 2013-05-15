
function AddFileInput()
{
	var counter = document.getElementById("files_counter").value;
	var table = document.getElementById("files_table_"+counter);

	document.getElementById("files_counter").value = ++counter;
	table.innerHTML += '<input name="FILE_'+counter+'" size="30" type="file"><br /><span id="files_table_'+counter+'"></span>';
}


function SupQuoteMessage()
{
	var selection;
	if (document.getSelection)
	{
		selection = document.getSelection();
		selection = selection.replace(/\r\n\r\n/gi, "_newstringhere_");
		selection = selection.replace(/\r\n/gi, " ");
		selection = selection.replace(/  /gi, "");
		selection = selection.replace(/_newstringhere_/gi, "\r\n\r\n");
	}
	else
	{
		selection = document.selection.createRange().text;
	}

	if (selection!="")
		document.forms["support_edit"].elements["MESSAGE"].value += "<QUOTE>"+selection+"</QUOTE>\n";
}

var QUOTE_open = 0;
var CODE_open = 0;
var B_open = 0;
var I_open = 0;
var U_open = 0;

var myAgent   = navigator.userAgent.toLowerCase();
var myVersion = parseInt(navigator.appVersion);
var myVersion = parseInt(navigator.appVersion);
var is_ie  = ((myAgent.indexOf("msie") != -1)  && (myAgent.indexOf("opera") == -1));
var is_nav = ((myAgent.indexOf('mozilla')!=-1) && (myAgent.indexOf('spoofer')==-1)
 && (myAgent.indexOf('compatible') == -1) && (myAgent.indexOf('opera')==-1)
 && (myAgent.indexOf('webtv')==-1) && (myAgent.indexOf('hotjava')==-1));

var is_win = ((myAgent.indexOf("win")!=-1) || (myAgent.indexOf("16bit")!=-1));
var is_mac = (myAgent.indexOf("mac")!=-1);


function insert_tag(thetag, objTextarea)
{
	var tagOpen = eval(thetag + "_open");
	if (tagOpen == 0)
	{
		if (DoInsert(objTextarea, "<"+thetag+">", "</"+thetag+">"))
		{
			eval(thetag + "_open = 1");
			eval("document.forms['support_edit'].elements['"+thetag+"'].value += '*'");
			//eval("document.form1." + thetag + ".value += '*'");
		}
	}
	else
	{
		DoInsert(objTextarea, "</"+thetag+">", "");
		//eval("document.form1." + thetag + ".value = ' " + eval(thetag + "_title") + " '");

		var buttonText = eval("document.forms['support_edit'].elements['"+thetag+"'].value");
		eval("document.forms['support_edit'].elements['"+thetag+"'].value = '"+(buttonText.slice(0,-1))+"'");

		eval(thetag + "_open = 0");
	}
}

function mozillaWr(textarea, open, close)
{
	var selLength = textarea.textLength;
	var selStart = textarea.selectionStart;
	var selEnd = textarea.selectionEnd;
	
	if (selEnd == 1 || selEnd == 2)
	selEnd = selLength;

	var s1 = (textarea.value).substring(0,selStart);
	var s2 = (textarea.value).substring(selStart, selEnd)
	var s3 = (textarea.value).substring(selEnd, selLength);
	textarea.value = s1 + open + s2 + close + s3;

	textarea.selectionEnd = 0;
	textarea.selectionStart = selEnd + open.length + close.length;
	return;
}


function DoInsert(objTextarea, Tag, closeTag)
{
	var isOpen = false;

	//if (closeTag=="")
		//isOpen = true;

	if ( myVersion >= 4 && is_ie && is_win && objTextarea.isTextEdit)
	{
		objTextarea.focus();
		var sel = document.selection;
		var rng = sel.createRange();
		rng.colapse;
		if ((sel.type=="Text" || sel.type=="None") && rng != null)
		{
			if (closeTag!="")
			{
				if (rng.text.length > 0) 
					Tag += rng.text + closeTag; 
				else
					isOpen = true;
			}
			rng.text = Tag;
		}
	}
	else
	{
		if (is_nav && document.getElementById)
		{
			if (closeTag!="" && objTextarea.selectionEnd > objTextarea.selectionStart)
			{
				mozillaWr(objTextarea, Tag, closeTag);
				isOpen = false;
			}
			else
			{
				mozillaWr(objTextarea, Tag, '');
				isOpen = true;
			}
		}
		else
		{
			objTextarea.value += Tag;
			isOpen = true;
		}

		//isOpen = true;
		//objTextarea.value += Tag;
	}



	objTextarea.focus();
	return isOpen;
}

// С‚СЂР°РЅСЃР»РёС‚РёСЂР°С†РёСЏ

//var TRANSLIT_title = "<?=GetMessage("SUP_TRANSLIT")?>";
var TRANSLIT_way = 0;

var smallEngLettersReg = new Array(/e'/g, /ch/g, /sh/g, /yo/g, /jo/g, /zh/g, /yu/g, /ju/g, /ya/g, /ja/g, /a/g, /b/g, /v/g, /g/g, /d/g, /e/g, /z/g, /i/g, /j/g, /k/g, /l/g, /m/g, /n/g, /o/g, /p/g, /r/g, /s/g, /t/g, /u/g, /f/g, /h/g, /c/g, /w/g, /~/g, /y/g, /'/g);
var capitEngLettersReg = new Array( /E'/g, /CH/g, /SH/g, /YO/g, /JO/g, /ZH/g, /YU/g, /JU/g, /YA/g, /JA/g, /A/g, /B/g, /V/g, /G/g, /D/g, /E/g, /Z/g, /I/g, /J/g, /K/g, /L/g, /M/g, /N/g, /O/g, /P/g, /R/g, /S/g, /T/g, /U/g, /F/g, /H/g, /C/g, /W/g, /~/g, /Y/g, /'/g);
var smallRusLetters = new Array("СЌ", "С‡", "С€", "С‘", "С‘", "Р¶", "СЋ", "СЋ", "СЏ", "СЏ", "Р°", "Р±", "РІ", "Рі", "Рґ", "Рµ", "Р·", "Рё", "Р№", "Рє", "Р»", "Рј", "РЅ", "Рѕ", "Рї", "СЂ", "СЃ", "С‚", "Сѓ", "С„", "С…", "С†", "С‰", "СЉ", "С‹", "СЊ");
var capitRusLetters = new Array( "Р­", "Р§", "РЁ", "РЃ", "РЃ", "Р–", "Р®", "Р®", "\РЇ", "\РЇ", "Рђ", "Р‘", "Р’", "Р“", "Р”", "Р•", "Р—", "Р", "Р™", "Рљ", "Р›", "Рњ", "Рќ", "Рћ", "Рџ", "Р ", "РЎ", "Рў", "РЈ", "Р¤", "РҐ", "Р¦", "Р©", "РЄ", "Р«", "Р¬");

var smallEngLetters = new Array("e", "ch", "sh", "yo", "jo", "zh", "yu", "ju", "ya", "ja", "a", "b", "v", "g", "d", "e", "z", "i", "j", "k", "l", "m", "n", "o", "p", "r", "s", "t", "u", "f", "h", "c", "w", "~", "y", "'");
var capitEngLetters = new Array("E", "CH", "SH", "YO", "JO", "ZH", "YU", "JU", "YA", "JA", "A", "B", "V", "G", "D", "E", "Z", "I", "J", "K", "L", "M", "N", "O", "P", "R", "S", "T", "U", "F", "H", "C", "W", "~", "Y", "'");
var smallRusLettersReg = new Array(/СЌ/g, /С‡/g, /С€/g, /С‘/g, /С‘/g,/Р¶/g, /СЋ/g, /СЋ/g, /СЏ/g, /СЏ/g, /Р°/g, /Р±/g, /РІ/g, /Рі/g, /Рґ/g, /Рµ/g, /Р·/g, /Рё/g, /Р№/g, /Рє/g, /Р»/g, /Рј/g, /РЅ/g, /Рѕ/g, /Рї/g, /СЂ/g, /СЃ/g, /С‚/g, /Сѓ/g, /С„/g, /С…/g, /С†/g, /С‰/g, /СЉ/g, /С‹/g, /СЊ/ );
var capitRusLettersReg = new Array(/Р­/g, /Р§/g, /РЁ/g, /РЃ/g, /РЃ/g, /Р–/g, /Р®/g, /Р®/g, /РЇ/g, /РЇ/g, /Рђ/g, /Р‘/g, /Р’/g, /Р“/g, /Р”/g, /Р•/g, /Р—/g, /Р/g, /Р™/g, /Рљ/g, /Р›/g, /Рњ/g, /Рќ/g, /Рћ/g, /Рџ/g, /Р /g, /РЎ/g, /Рў/g, /РЈ/g, /Р¤/g, /РҐ/g, /Р¦/g, /Р©/g, /РЄ/g, /Р«/g, /Р¬/);

// СЌ, С‡, С€, С‘, С‘,Р¶, СЋ, СЋ, СЏ, СЏ, Р°, Р±, РІ, Рі, Рґ, Рµ, Р·, Рё, Р№, Рє, Р», Рј, РЅ, Рѕ, Рї, СЂ, СЃ, С‚, Сѓ, С„, С…, С†, С‰, СЉ, С‹, СЊ
// e, ch, sh, yo, jo, zh, yu, ju, ya, ja, a, b, v, g, d, e, z, i, j, k, l, m, n, o, p, r, s, t, u, f, h, c, w, ~, y, '

function translit(objTextarea)
{
	var i;
	var textbody = objTextarea.value;
	var selected = false;

	if (objTextarea.isTextEdit)
	{
		objTextarea.focus();
		var sel = document.selection;
		var rng = sel.createRange();
		rng.colapse;
		if (sel.type=="Text" && rng != null)
		{
			textbody = rng.text;
			selected = true;
		}
	}

	if (textbody)
	{
		if (TRANSLIT_way==0) // Р»Р°С‚РёРЅРёС†Р° -> РєРёСЂРёР»Р»РёС†Р°
		{
			for (i=0; i<smallEngLettersReg.length; i++) textbody = textbody.replace(smallEngLettersReg[i], smallRusLetters[i]);
			for (i=0; i<capitEngLettersReg.length; i++) textbody = textbody.replace(capitEngLettersReg[i], capitRusLetters[i]);
		}
		else // РєРёСЂРёР»Р»РёС†Р° -> Р»Р°С‚РёРЅРёС†Р°
		{
			for (i=0; i<smallRusLetters.length; i++) textbody = textbody.replace(smallRusLettersReg[i], smallEngLetters[i]);
			for (i=0; i<capitRusLetters.length; i++) textbody = textbody.replace(capitRusLettersReg[i], capitEngLetters[i]);
		}
		if (!selected) objTextarea.value = textbody;
		else rng.text = textbody;
	}

	if (TRANSLIT_way==0) // Р»Р°С‚РёРЅРёС†Р° -> РєРёСЂРёР»Р»РёС†Р°
	{
		//document.form1.TRANSLIT.value += " *";
		document.forms['support_edit'].elements['TRANSLIT'].value += " *";
		TRANSLIT_way = 1;
	}
	else // РєРёСЂРёР»Р»РёС†Р° -> Р»Р°С‚РёРЅРёС†Р°
	{
		//document.form1.TRANSLIT.value = TRANSLIT_title;
		document.forms['support_edit'].elements['TRANSLIT'].value = document.forms['support_edit'].elements['TRANSLIT'].value.slice(0,-2);
		TRANSLIT_way = 0;
	}
	objTextarea.focus();
}
