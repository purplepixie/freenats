var openNode = "";

function nodeClick(nodeid)
{
if (openNode==nodeid)
	{ // already open so goto node page...
	window.location.href = "node.php?nodeid=" + nodeid;
	return 0;
	}
openNode = nodeid;
popup = document.getElementById('popup_div');
popup.innerHTML = popupTop + "Loading..." + popupBottom;
popup.style.left = (MouseX-10) + 'px';
popup.style.top = MouseY + 'px';
popup.style.display = 'block';

var xmlhttp=false;
if (window.XMLHttpRequest)
	{
	xmlhttp=new XMLHttpRequest();
	if (xmlhttp.overrideMimeType) xmlhttp.overrideMimeType('text/xml');
	}
else if (window.ActiveXObject)
	{
	try
		{
		xmlhttp = new ActiveXObject("Msxml2.HTTP");
		}
	catch(e)
		{
		xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
		}
	}

if (!xmlhttp)
	{
	alert("Cannot Create XML HTTP Request Object");
	return false;
	}

xmlhttp.onreadystatechange = function()
	{
	if (xmlhttp.readyState == 4)
		{
		if (xmlhttp.status == 200)
			{
			popup.innerHTML = popupTop + xmlhttp.responseText + popupBottom;
			}
		else
			{
			popup.innerHTML = popupTop + "Error Code Returned: " + xmlhttp.status + popupBottom;
			}
		}
	}
	
var url="monitor.popup.php?type=node&nodeid="+nodeid;
xmlhttp.open("GET",url,true);
xmlhttp.send(null);


}

function closePopup()
{
openNode="";
document.getElementById('popup_div').style.display = 'none';
}

var popupTop = "<div class=\"popup_top\"><a href=\"javascript:closePopup();\">X</a></div>";
var popupBottom = "<div class=\"popup_bottom\"><a href=\"javascript:closePopup();\">close</a></div>";

var isIE = false;
if (document.all) isIE=true;

var MouseX=0;
var MouseY=0;

function recordMouse(e)
{
if (!e) e=window.event;
if (isIE)
	{
	MouseX = e.clientX + document.body.scrollLeft;
	MouseY = e.clientY + document.body.scrollTop;
	}
else
	{
	MouseX = e.pageX;
	MouseY = e.pageY;
	}
}

if (!isIE) document.captureEvents(Event.MOUSEMOVE);

if (window.addEventListener) document.addEventListener("mousemove",recordMouse,false);
else if (window.attachEvent) document.attachEvent("onmousemove",recordMouse);
else document.onmousemove = recordMouse;

