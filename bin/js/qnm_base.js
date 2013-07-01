var bEdited=false;
function qtHtmldecode(str)
{
	var ta=document.createElement("textarea");
	ta.innerHTML=str.replace("<","&lt;").replace(">","&gt;");
	return ta.value;
}
function qtFocusEnd(id)
{
	var doc = document;
	if ( doc.getElementById(id) )
	{
	var str = doc.getElementById(id).value;
	doc.getElementById(id).value="";
	doc.getElementById(id).focus();
	doc.getElementById(id).value=str;
	}
}
function qtKeypress(e,s)
{
	if (window.event)
	{
	if (e.keyCode==13) document.getElementById(s).click();
	}
	else if(e.which)
	{
	if (e.which==13) document.getElementById(s).click();
	}
	return null;
}
function qtEdited(bEdited,e)
{
	if (typeof(bEdited)==='undefined' || !bEdited || e==="") return true;
	if (typeof(e)==='undefined' || e==0) e="Data not yet saved. Quit without saving?";
	if (!confirm(qtHtmldecode(e))) return false;
	return true;
}
function qtVmail(id)
{
	var doc = document;
	var str = doc.getElementById('href'+id).href;
	str = str.replace(/-at-/g,'@');
	str = str.replace(/-dot-/g,'.');
	str = str.replace('java:','mailto:');
	doc.getElementById('href'+id).href = str;
	if ( doc.getElementById('img'+id) )
	{
	str = doc.getElementById('img'+id).title;
	str = str.replace(/-at-/g,'@');
	str = str.replace(/-dot-/g,'.');
	doc.getElementById('img'+id).title = str;
	}
	return null;
}
function qtHmail(id)
{
	var doc = document;
	var str = doc.getElementById('href'+id).href;
	str = str.replace(/\@/g,'-at-');
	str = str.replace(/\./g,'-dot-');
	str = str.replace('javamail:','mailto:');
	doc.getElementById('href'+id).href = str;
	if ( doc.getElementById('img'+id) )
	{
	str = doc.getElementById('img'+id).title;
	str = str.replace(/\@/g,'-at-');
	str = str.replace(/\./g,'-dot-');
	doc.getElementById('img'+id).title = str;
	}
	return null;
}
function qtWritemailto(str1,str2,separator)
{
	var doc = document;
	doc.write('<a class="small" href="mailto:' + str1 + '@' + str2 + '">');
	doc.write(str1 + '@' + str2);
	doc.write('<\/a>');
	if ( separator ) doc.write(separator);
	return null;
}
