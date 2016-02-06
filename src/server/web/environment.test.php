<?php
/* -------------------------------------------------------------
This file is part of FreeNATS

FreeNATS is (C) Copyright 2008-2010 PurplePixie Systems

FreeNATS is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

FreeNATS is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with FreeNATS.  If not, see www.gnu.org/licenses

For more information see www.purplepixie.org/freenats
-------------------------------------------------------------- */

function test_funcs($funcs)
{
if (!is_array($funcs)) $funcs=array($funcs);
$out="";
for($a=0; $a<count($funcs); $a++)
	{
	if (!function_exists($funcs[$a]))
		{
		$out.=$funcs[$a]." ";
		}
	}
if ($out!="") $out.="functions required";
return $out;
}

function test_mod($mod,$funcs)
{
$text=test_funcs($funcs);
if ($text=="")
	{
	echo "<b style=\"color: green;\">".$mod."</b> - Ok<br>";
	return true;
	}
else
	{
	echo "<b style=\"color: red;\">".$mod."</b> - ".$text."<br>";
	return false;
	}
}

if (!isset($env_test_web)) // PHP CLI Test
{
if (!test_mod("IMAP","imap_open"))
	{
	echo "IMAP is not supported - don't configure IMAP tests or your environment will hang<br>";
	}
if (!test_mod("Sockets","fsockopen"))
	{
	echo "Sockets are not supported - fatal error<br>";
	}
if (!test_mod("Streams","fopen"))
	{
	echo "Streams are not supported - fatal error<br>";
	}
if (!test_mod("MySQL","mysql_connect"))
	{
	echo "MySQL is not supported - fatal error<br>";
	}
if (!test_mod("Mail","mail"))
	{
	echo "mail() not supported, may have to use direct SMTP or maybe no mail will work!<br>";
	}
if (!test_mod("PHP5","str_split"))
	{
	echo "Don't appear to be running PHP5 - FreeNATS may not work on PHP4<br>";
	}
if (!test_mod("XML","xml_parser_create"))
	{
	echo "XML Parser not present in PHP CLI - nodeside testing and anything else dependent on XML (discovery/import tools etc) will fail!<br>";
	}

if (isset($_SERVER['argv'][1]) && ($_SERVER['argv'][1]=="full"))
	{
	echo "<br><br><DIV STYLE=\"font-family: monospace;\">";
	ob_start();
	phpinfo();
	$info=ob_get_contents();
	ob_clean();
	$info=nl2br($info);
	echo $info;
	echo "</DIV><br>";
	}
exit(1);
}
else // PHP Web/Apache Module Test
{
if (!test_mod("MySQL","mysql_connect"))
	{
	echo "MySQL is not supported - fatal error<br>";
	}
if (!test_mod("Mail","mail"))
	{
	echo "mail() not supported, may have to use direct SMTP or maybe no mail will work!<br>";
	}
if (!test_mod("PHP5","str_split"))
	{
	echo "Don't appear to be running PHP5 - FreeNATS may not work on PHP4<br>";
	}
if (!test_mod("GD Graphics",array("imagecreate","imagepng")))
	{
	echo "GD Graphics does not appear to be supported or does not support PNG - historic graphs will not work!<br>";
	}
if (!test_mod("XML","xml_parser_create"))
	{
	echo "XML Parser not present in PHP web - nodeside and other functions from within the web interface will fail<br>";
	}
}

?>
