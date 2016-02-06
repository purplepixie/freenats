<!--
/* -------------------------------------------------------------
This file is part of FreeNATS

FreeNATS is (C) Copyright 2008-2011 PurplePixie Systems

FreeNATS is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

FreeNATS is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Foobar.  If not, see www.gnu.org/licenses

For more information see www.purplepixie.org/freenats
-------------------------------------------------------------- */

// Help Popup
function freenats_help( helpid )
{
var url= 'help.php?id='+helpid;
var opt= 'width=300,height=200,toolbar=no,directories=no,status=no,copyhistory=no,left=0,top=20,screenX=0,screenY=20';
var tit= 'FreeNATS Help: '.helpid;

window.open(url,tit,opt);
}

// Display Toggles
function displayToggle( id )
{
	displayBlockToggle(id);
}

function displayBlockToggle( id )
{
	var ele = document.getElementById(id);
	if (ele.style.display == "block") ele.style.display="none";
	else ele.style.display="block";
}

function confirmGo( text, url )
{
	if (confirm(text)) window.location = url;
}
//-->