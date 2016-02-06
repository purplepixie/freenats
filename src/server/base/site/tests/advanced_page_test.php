<?php // advanced_page_test.php v 0.03 17/08/2009
/* -------------------------------------------------------------
This file is part of FreeNATS

FreeNATS is (C) Copyright 2008-2009 PurplePixie Systems

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

/* Description:

This is a test add-on mobule for FreeNATS version 1

USAGE INSTRUCTIONS:

Place into the server/base/site/tests directory being sure to keep a .php
extension on the end of the file. Enable the system variable site.include.testss
(set to 1) to enable inclusion.

The advanced page check is configured through the standard test management
interface

*/


function extended_page_checker($url,$text,$notext,$user="",$pass="",$timeout=-1) // $text and $notext are arrays in this instance
	{
	global $NATS;
	
	$timer=new TFNTimer(); // initialise the timer
	url_lookup($url); // pre-resolve the DNS into cache

	$output=""; // output buffer

	if ($user!="") // use HTTP-AUTH
		{
		$pos=strpos($url,"://");
		if ($pos===false) return -1; // not a valid URL
		$protocol=substr($url,0,$pos+3); // protocol section
		$uri=substr($url,$pos+3); // uri section
		$url=$protocol.$user.":".$pass."@".$uri; // make http://user:pass@uri
		}
		
	if ($timeout<=0) // use NATS or env
		{
		if (isset($NATS))
			{
			$nto=$NATS->Cfg->Get("test.http.timeout",-1);
			if ($nto>0) $timeout=$nto; // use NATS timeout
			}
		}
	if ($timeout>0) // use the set timeout
		$oldtimeout=ini_set("default_socket_timeout",$timeout);
		
	$timer->Start();
		
	if (function_exists("curl_getinfo")) // use CURL if present
		{
		$ch=curl_init();
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);
		curl_setopt($ch,CURLOPT_HEADER,1);
		if ($timeout>0) curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
		if ($timeout>0) curl_setopt($ch,CURLOPT_TIMEOUT,$timeout);
		if (!$output=curl_exec($ch))
			{
			$ctr=-1; // failed
			}
		else $ctr=round(curl_getinfo($ch,CURLINFO_SIZE_DOWNLOAD)/1024,2);
		curl_close($ch);
		
		if ($ctr==0) $ctr="0.0001";
		
		}
	else
		{	// no CURL - use fopen()	
		$fp=@fopen($url,"r");
		if ($fp<=0)
			{
			if ($timeout>0) ini_set("default_socket_timeout",$oldtimeout);
			return -1;
			}
		$ctr=0;
		while ($output.=@fgets($fp,1024)) $ctr+=sizeof($body);
		@fclose($fp);
		}
		
	if ($ctr<0) return $ctr; // negative number (-1) failed to open
	
	$elapsed=$timer->Stop();
	
	if ($timeout>0) ini_set("default_socket_timeout",$oldtimeout);
	
	// now to check the actual text
	if (is_array($text))
		{
		foreach($text as $findthis)
			{
			if ($findthis!="")
				if (strpos($output,$findthis)===false) return -2; // text to be found not found
			}
		}
	if (is_array($notext))
		{
		foreach($notext as $donotfindthis)
			{
			if ($donotfindthis!="")
				if (strpos($output,$donotfindthis)!==false) return -3; // text not to find found
			}
		}
	
	return $elapsed;
	}


global $NATS;

class Advanced_Pagecheck_Test extends FreeNATS_Local_Test
{

 function DoTest($testname,$param,$hostname,$timeout,$params)
 {
 /* parameters:   -- sadly very messy but to do otherwise would break plug+play
 0: url
 1: text
 4: notext
 2: user
 3: pass

 5: text
 6: text
 7: notext
 8: notext
 9: NULL
 */
 $text=array( $params[1], $params[5], $params[6] );
 $notext=array( $params[4], $params[7], $params[8] ); 

 $result=extended_page_checker($params[0],$text,$notext,$params[2],$params[3],$timeout);
 return $result;
 }

 function Evaluate($result) 
 {
 if ($result>0) return 0; // FreeNATS passed (0) flag if > 0
 return 2; // FreeNATS failed (2) flag ( <= 0 )
 }

 function ProtectOutput(&$test)
 {
 $test['testparam3']=""; // blank password for output
 return true;
 }

 function DisplayForm(&$test)
 {
 $out="";
 $out.="<table width=100% border=0>";
 $out.="<tr><td align=right valign=top>URL :</td>";
 $out.="<td align=left>";
 $out.="<input type=text size=30 name=testparam value=\"".$test['testparam']."\">";
 $out.="<br><i>Fully-qualified URL including http://</i>";
 $out.="</td></tr>";
 $out.="<tr><td align=right valign=top>Strings :</td>";
 $out.="<td align=left>";
 $out.="<input type=text size=30 name=testparam1 value=\"".$test['testparam1']."\"><br />";
 $out.="<input type=text size=30 name=testparam5 value=\"".$test['testparam5']."\"><br />";
 $out.="<input type=text size=30 name=testparam6 value=\"".$test['testparam6']."\">";
 $out.="<br><i>String(s) to search for - all defined must<br />be found for the test to pass<br />";
 $out.="Blank strings are ignored.</i>";
 $out.="</td></tr>";
 $out.="<tr><td align=right valign=top>No Strings :</td>";
 $out.="<td align=left>";
 $out.="<input type=text size=30 name=testparam4 value=\"".$test['testparam4']."\"><br />";
 $out.="<input type=text size=30 name=testparam7 value=\"".$test['testparam7']."\"><br />";
 $out.="<input type=text size=30 name=testparam8 value=\"".$test['testparam8']."\">"; 
 $out.="<br><i>String(s) to NOT find - fails if any are present<br>Leave blank to not use this portion of the test</i>";
 $out.="</td></tr>";

 $out.="<tr><td align=right valign=top>Username :</td>";
 $out.="<td align=left>";
 $out.="<input type=text size=30 name=testparam2 value=\"".$test['testparam2']."\">";
 $out.="<br><i>Specify to use HTTP-AUTH on the URL</i>";
 $out.="</td></tr>"; 

 $out.="<tr><td align=right valign=top>Password :</td>";
 $out.="<td align=left>";
 $out.="<input type=text size=30 name=testparam3 value=\"\">"; // dont display it
 $out.="<input type=hidden name=keepparam3 value=1>"; // don't update testparam3 (if blank)
 $out.="<br><i>Enter a new password to set or... ";
 $out.="<input type=checkbox name=clearparam3 value=1> "; // clears testparam3 if set
 $out.="clear it</i>";
 $out.="</td></tr>";
 echo $out; // output the buffer
 }
}

// Now we have defined the class we must register it with FreeNATS

$params=array(); // blank parameters array as we have implemented DisplayForm above

$NATS->Tests->Register(
 "advpagecheck",           // the internal simple test name (must not conflict with anything else)
 "Advanced_Pagecheck_Test",      // the class name (above)
 $params,               // parameters (blank for now)
 "Web Content Test", // the display name of the test in the interface
 3,                     // the revision number of the test
 "Advanced Page Checker");   // extended description for the test module used in overview

?>
