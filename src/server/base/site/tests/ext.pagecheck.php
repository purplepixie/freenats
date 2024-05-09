<?php
/* FreeNATS Extended Page Check v2 - with CURL */

function extended_check_page_text_2($url, $text, $notext, $user = "", $pass = "", $timeout = -1) // $text and $notext are arrays in this instance
{
	global $NATS;

	$timer = new TFNTimer(); // initialise the timer
	url_lookup($url); // pre-resolve the DNS into cache

	$output = ""; // output buffer

	if ($user != "") // use HTTP-AUTH
	{
		$pos = strpos($url, "://");
		if ($pos === false) return -1; // not a valid URL
		$protocol = substr($url, 0, $pos + 3); // protocol section
		$uri = substr($url, $pos + 3); // uri section
		$url = $protocol . $user . ":" . $pass . "@" . $uri; // make http://user:pass@uri
	}

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

	if (function_exists("curl_getinfo")) // use CURL if present
	{
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
	} else {	// no CURL - use fopen()	
		$fp = @fopen($url, "r");
		if ($fp <= 0) {
			if ($timeout > 0) ini_set("default_socket_timeout", $oldtimeout);
			return -1;
		}
		$ctr = 0;
		while ($output .= @fgets($fp, 1024)) $ctr += strlen($output);
		@fclose($fp);
	}

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


/* // The old version
function extended_check_page_text($url,$text,$notext="",$user="",$pass="")
{ 
 $timer=new TFNTimer(); // initialise the timer
 url_lookup($url); // pre-resolve the DNS into cache

 if ($user!="") // use HTTP-AUTH
  {
  $pos=strpos($url,"://");
  if ($pos===false) return -1; // not a valid URL
  $protocol=substr($url,0,$pos+3); // protocol section
  $uri=substr($url,$pos+3); // uri section
  $url=$protocol.$user.":".$pass."@".$uri; // make http://user:pass@uri
  }
 $timer->Start(); // start the timer
 $fp=fopen($url,"r"); // open the URL
 if ($fp<=0) return false; // fail if can't open
 // or we could start it here to not include opening times...
 $body="";
 while (!feof($fp))
  $body.=fgets($fp,1024); // read into the body in 1k chunks
 $elapsed=$timer->Stop(); // get the elapsed time at this point
 fclose($fp); // finished with pointer
 if (strpos($body,$text)===false) return -1; // not found
 if ($notext!="") // the text we do NOT want to appear is $notext - check if set
 	{
	if (strpos($body,$notext)!==false) return -2; // was found in the page
	}
 return $elapsed; // return a positive elapsed time on success
}
*/

global $NATS;

class Ext_Pagecheck_Test extends FreeNATS_Local_Test
{

	function DoTest($testname, $param, $hostname = "", $timeout = -1, $params = false)
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
		$text = array($params[1], $params[5], $params[6]);
		$notext = array($params[4], $params[7], $params[8]);

		$result = extended_check_page_text_2($params[0], $text, $notext, $params[2], $params[3], $timeout);
		return $result;
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

	function DisplayForm(&$test) // nice user form (optional)
	{
		$out = ""; // output buffer
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

		// so far so much the same... but...
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

$NATS->Tests->Register(
	"extpagecheck",           // the internal simple test name (must not conflict with anything else)
	"Ext_Pagecheck_Test",      // the class name (above)
	$params,               // parameters (blank for now)
	"Page Content Checker", // the display name of the test in the interface
	1,                     // the revision number of the test
	"Extended Page Checker"
);   // extended description for the test module used in overview
