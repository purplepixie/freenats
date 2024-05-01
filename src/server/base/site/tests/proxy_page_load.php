<?php // proxy_page_load.php v 0.01 30/09/2009
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

The proxy page loader is configured through the standard test management
interface

*/


global $NATS;

class Proxy_Page_Test extends FreeNATS_Local_Test
{

	function DoTest($testname, $param, $hostname = "", $timeout = -1, $params = false)
	{
		global $NATS;
		/* parameters:
 0: url (FQDN URL can include user:pass@host or host:port formats)
 1: proxy server
 2: proxy port
 3: proxy username
 4: proxy password
 5: proxy type (http or socks5)
 6:
 7:
 8:
 9:
 */
		// setup variables
		$url = $params[0];
		$proxy = $params[1];
		$proxy_port = $params[2];
		if ($proxy != "") $use_proxy = true;
		else $use_proxy = false;
		$proxy_user = $params[3];
		$proxy_pass = $params[4];
		if ($proxy_user != "") $proxy_auth = true;
		else $proxy_auth = false;


		$timer = new TFNTimer(); // initialise the timer
		url_lookup($url); // pre-resolve the DNS into cache

		$output = ""; // output buffer


		if ($timeout <= 0) // use NATS or env
		{
			if (isset($NATS)) {
				$nto = $NATS->Cfg->Get("test.http.timeout", -1);
				if ($nto > 0) $timeout = $nto; // use NATS timeout
			}
		}
		if ($timeout > 0) // use the set timeout
			$oldtimeout = ini_set("default_socket_timeout", $timeout);

		$timer->Start();

		// Requires CURL Now

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		if ($timeout > 0) curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		if ($timeout > 0) curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		if (!$output = curl_exec($ch)) {
			$ctr = -1; // failed
		} else $ctr = round(curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD) / 1024, 2);
		curl_close($ch);

		if ($ctr == 0) $ctr = "0.0001";



		if ($ctr < 0) return $ctr; // negative number (-1) failed to open

		$elapsed = $timer->Stop();

		if ($timeout > 0) ini_set("default_socket_timeout", $oldtimeout);

		// now to check the actual text
		if (is_array($text)) {
			foreach ($text as $findthis) {
				if ($findthis != "")
					if (strpos($output, $findthis) === false) return -2; // text to be found not found
			}
		}
		if (is_array($notext)) {
			foreach ($notext as $donotfindthis) {
				if ($donotfindthis != "")
					if (strpos($output, $donotfindthis) !== false) return -3; // text not to find found
			}
		}

		return $elapsed;
	}

	function Evaluate($result)
	{
		if ($result > 0) return 0; // FreeNATS passed (0) flag if > 0
		return 2; // FreeNATS failed (2) flag ( <= 0 )
	}

	function ProtectOutput(&$test)
	{
		$test['testparam4'] = ""; // blank proxy password for output
		return true;
	}

	function DisplayForm(&$test)
	{
		$out = "";
		$out .= "<table width=100% border=0>";
		$out .= "<tr><td align=right valign=top>URL :</td>";
		$out .= "<td align=left>";
		$out .= "<input type=text size=30 name=testparam value=\"" . $test['testparam'] . "\">";
		$out .= "<br><i>Fully-qualified URL including http://</i>";
		$out .= "</td></tr>";
		$out .= "<tr><td align=right valign=top>Strings :</td>";
		$out .= "<td align=left>";
		$out .= "<input type=text size=30 name=testparam1 value=\"" . $test['testparam1'] . "\"><br />";
		$out .= "<input type=text size=30 name=testparam5 value=\"" . $test['testparam5'] . "\"><br />";
		$out .= "<input type=text size=30 name=testparam6 value=\"" . $test['testparam6'] . "\">";
		$out .= "<br><i>String(s) to search for - all defined must<br />be found for the test to pass<br />";
		$out .= "Blank strings are ignored.</i>";
		$out .= "</td></tr>";
		$out .= "<tr><td align=right valign=top>No Strings :</td>";
		$out .= "<td align=left>";
		$out .= "<input type=text size=30 name=testparam4 value=\"" . $test['testparam4'] . "\"><br />";
		$out .= "<input type=text size=30 name=testparam7 value=\"" . $test['testparam7'] . "\"><br />";
		$out .= "<input type=text size=30 name=testparam8 value=\"" . $test['testparam8'] . "\">";
		$out .= "<br><i>String(s) to NOT find - fails if any are present<br>Leave blank to not use this portion of the test</i>";
		$out .= "</td></tr>";

		$out .= "<tr><td align=right valign=top>Username :</td>";
		$out .= "<td align=left>";
		$out .= "<input type=text size=30 name=testparam2 value=\"" . $test['testparam2'] . "\">";
		$out .= "<br><i>Specify to use HTTP-AUTH on the URL</i>";
		$out .= "</td></tr>";

		$out .= "<tr><td align=right valign=top>Password :</td>";
		$out .= "<td align=left>";
		$out .= "<input type=text size=30 name=testparam3 value=\"\">"; // dont display it
		$out .= "<input type=hidden name=keepparam3 value=1>"; // don't update testparam3 (if blank)
		$out .= "<br><i>Enter a new password to set or... ";
		$out .= "<input type=checkbox name=clearparam3 value=1> "; // clears testparam3 if set
		$out .= "clear it</i>";
		$out .= "</td></tr>";
		echo $out; // output the buffer
	}
}

// Now we have defined the class we must register it with FreeNATS

$params = array(); // blank parameters array as we have implemented DisplayForm above

if (function_exists("curl_getinfo"))  // register if CURL exists
{
	$NATS->Tests->Register(
		"proxypage",           // the internal simple test name (must not conflict with anything else)
		"Proxy_Page_Test",      // the class name (above)
		$params,               // parameters (blank for now)
		"Web Proxy Test", // the display name of the test in the interface
		3,                     // the revision number of the test
		"Proxy Page Test"
	);   // extended description for the test module used in overview
	$NATS->Tests->SetUnits("proxypage", "Seconds", "s");
} else { // display appropriate error
	$NATS->Event("Proxy Test Not Loaded as CURL Not Supported", 3, "Proxy Test", "Extras");
}
