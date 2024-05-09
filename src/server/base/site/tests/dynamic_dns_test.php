<?php // dynamic_dns_test.php v 0.01 14/11/2010
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

This test is configured through the standard test management interface

*/


function get_external_ip($url = "")
{
	global $NATS;

	if ($url == "") $url = "http://xml.purplepixie.org/apps/ipaddress/?format=plain";

	if (function_exists("curl_getinfo")) // use CURL if present
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		if (!$output = curl_exec($ch)) {
			$ctr = -1; // failed
		} else $ctr = round(curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD) / 1024, 2);
		curl_close($ch);

		if ($ctr == 0) $ctr = "0.0001";
	} else {	// no CURL - use fopen()	
		$fp = @fopen($url, "r");
		if ($fp <= 0) {
			return -1;
		}
		$ctr = 0;
		$output = "";
		while ($output .= @fgets($fp, 1024)) $ctr += strlen($output);
		@fclose($fp);
	}

	if ($ctr < 0) return $ctr;
	else return $output;
}


global $NATS;

class Dynamic_DNS_Test extends FreeNATS_Local_Test
{

	function DoTest($testname, $param, $hostname = "", $timeout = -1, $params = false)
	{
		/* parameters: 
 0: hostname
 1: External IP Info Provider
    0 = PurplePixie
    1 = Other
 2: Other IP Info Provider URL
 */

		if ($params[1] == 0) $url = "";
		else $url = $params[2];

		$ip = get_external_ip($url);
		// echo "External: ".$ip."\n"; 

		if ($ip <= 0) return $ip;

		$dynamic_ip = gethostbyname($params[0]);
		// echo "Dynamic: ".$params[0]." = ".$dynamic_ip."\n"; 

		if ($dynamic_ip == $params[0]) return -2; // unmodified host; hostname lookup failed

		if ($dynamic_ip == $ip) return 1; // successful resolution and match
		else return 0; // External IP and Dynamic IP are not matching 
	}

	function Evaluate($result)
	{
		if ($result > 0) return 0; // FreeNATS passed (0) flag if > 0
		return 2; // FreeNATS failed (2) flag ( <= 0 )
	}

	function ProtectOutput(&$test)
	{
		$test['testparam3'] = ""; // blank password for output
		return true;
	}

	function DisplayForm(&$test)
	{
		$out = "";
		$out .= "<table width=100% border=0>";
		$out .= "<tr><td align=right valign=top>Hostname:</td>";
		$out .= "<td align=left>";
		$out .= "<input type=text size=30 name=testparam value=\"" . $test['testparam'] . "\">";
		$out .= "<br><i>Dynamic DNS Hostname to Check (i.e. myhost.dyndns.org)</i>";
		$out .= "</td></tr>";
		$out .= "<tr><td align=right valign=top>IP:</td>";
		$out .= "<td align=left>";
		$out .= "<input type=radio name=testparam1 value=0";
		if ($test['testparam1'] == 0) $out .= " checked";
		$out .= "> Use xml.purplepixie.org IP Address Service<br>";
		$out .= "<input type=radio name=testparam1 value=1";
		if ($test['testparam1'] == 1) $out .= " checked";
		$out .= "> URL: <input type=text name=testparam2 size=40 value=\"" . $test['testparam2'] . "\"><br>";
		$out .= "<i>This setting tells FreeNATS where to get your external IP from. You can either use ";
		$out .= "the PurplePixie XML App gateway or put in your own URL. The URL must return a plain IP.</i>";
		$out .= "</td></tr>";
		$out .= "</table>";
		echo $out; // output the buffer
	}
}

// Now we have defined the class we must register it with FreeNATS

$params = array(); // blank parameters array as we have implemented DisplayForm above

$NATS->Tests->Register(
	"dynamicdns",           // the internal simple test name (must not conflict with anything else)
	"Dynamic_DNS_Test",      // the class name (above)
	$params,               // parameters (blank for now)
	"Dynamic DNS Test", // the display name of the test in the interface
	1,                     // the revision number of the test
	"Check Dyanamic DNS Host Against External IP"
);   // extended description for the test module used in overview
