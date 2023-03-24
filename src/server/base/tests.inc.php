<?php // tests.inc.php
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

if (!isset($BaseDir))
	$BaseDir = "../base/"; // 
require_once($BaseDir . "timer.inc.php"); // just in case standalone

// FreeNATS_Local_Test Base Class - Local Tests (run from FreeNATS server) must extend from this

class FreeNATS_Local_Test
{
	function DoTest($testname, $param, $hostname = "", $timeout = -1, $params = false)
	{
		return 0;
	}

	function Evaluate($result)
	{
		return -1;
	}

	function DisplayForm(&$row)
	{
		return false;
	}

	function ProtectOutput(&$row)
	{
		return false;
	}
}

// ------------------------------------------------------------------------------------------------
require($BaseDir . "tests/tcp.inc.php");
require($BaseDir . "tests/udp.inc.php");
require($BaseDir . "tests/mysql.inc.php");
require($BaseDir . "tests/imap.inc.php");
require($BaseDir . "tests/smtp.inc.php");

require($BaseDir . "tests/dns.inc.php");
require($BaseDir . "tests/nats-dns.inc.php"); // the wrapper module

require($BaseDir . "tests/smb.inc.php");

require($BaseDir . "tests/ppping.inc.php");

require($BaseDir . "tests/ldap.inc.php");

require($BaseDir . "tests/nslast.inc.php");

function ip_lookup($hostname) // "safe" DNS lookup function to call with a hostname, URL or IP - returns 0 if unsuccessful
{
	// Is it already an IP adress?
	$out = str_replace(".", "", $hostname);
	if (is_numeric($out))
		return $hostname; // yes it is

	// No it is not
	$ip = @gethostbyname($hostname);
	if ($ip == $hostname)
		return 0; // unmodified host - lookup failed

	return $ip;
}

function next_run_x($interval)
{
	if ($interval < 1)
		return time();
	return time() + (($interval) * 60) - 30;
}

function test_sleep()
{
	global $NATS;
	if (!isset($NATS))
		return false;
	$sleep = $NATS->Cfg->Get("test.interval", 0);
	if ($sleep <= 0)
		return false;
	$sleep = $sleep * 1000000; // convert to usec
	usleep($sleep);
	return true;
}

function url_lookup($url)
{
	// Sod regular expressions here as we'd have to do it twice or with cleverness I lack
// Is it a URL?
	$colon = strpos($url, ":");
	if ($colon != 0) // exists so it a URL
	{
		$out = preg_match("@^(?:http[s]*://)?([^/|\?|:]+)@i", $url, $matches);
		$hostname = $matches[1];
	} else
		$hostname = $url; // try direct

	return ip_lookup($hostname);
}


function bin_str_dump($s, $count = 0)
{
	//$s = base_convert($s,10,2);
	$data = unpack('C*', $s);
	foreach ($data as $item) {
		echo ord($item) . " ";
	}
	echo "\n";
}



function PingTest($host, $ctimeout = -1)
{
	global $NATS;

	$returnsecs = true;
	if (isset($NATS)) {
		if ($NATS->Cfg->Get("test.icmp.returnms", 0) == 1)
			$returnsecs = false;
	}

	// Timeout Values
	if (isset($NATS))
		$timeout = $NATS->Cfg->Get("test.icmp.timeout", 10);
	else
		$timeout = 10;
	if ($ctimeout > 0)
		$timeout = $ctimeout; // use custom timeout if passed
	if ($timeout <= 0)
		$timeout = 10; // catch-all for defaults bug

	$ping = new PPPing();
	$ping->hostname = $host;
	$ping->timeout = $timeout;

	$result = $ping->Ping();

	if ($result < 0) // error condition
	{
		return $result;
	} else if ($result == 0) // zero time
	{
		$result = "0.0001";
	}

	if ($returnsecs) {
		$result = round($result / 1000, 3); // convert to seconds
		if ($result == 0)
			$result = 0.0001;
	}

	return $result;
}

function WebTest($url, $timeout = -1)
{
	global $NATS;
	if ($timeout <= 0) // use NATS or env
	{
		if (isset($NATS)) {
			$nto = $NATS->Cfg->Get("test.http.timeout", -1);
			if ($nto > 0)
				$timeout = $nto; // use NATS timeout
		}
	}
	if ($timeout > 0) // use the set timeout
		$oldtimeout = ini_set("default_socket_timeout", $timeout);

	if (function_exists("curl_getinfo")) // use CURL if present
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		if ($timeout > 0)
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		if ($timeout > 0)
			curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		if (!$output = curl_exec($ch)) {
			$ctr = -1; // failed
		} else
			$ctr = round(curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD) / 1024, 2);
		curl_close($ch);

		if ($ctr == 0)
			$ctr = "0.0001";

	} else { // no CURL - use fopen()	
		$oldagent = ini_set("user_agent", "MSIE 4\.0b2;"); // MSIE 4.0b2 is HTTP/1.0 only just like fopen http wrapper
		$fp = @fopen($url, "r");
		if ($fp <= 0) {
			if ($timeout > 0)
				ini_set("default_socket_timeout", $oldtimeout);
			ini_set("user_agent", $oldagent);
			return -1;
		}
		$ctr = 0;
		while ($body = @fgets($fp, 1024))
			$ctr += sizeof($body);
		@fclose($fp);
		ini_set("user_agent", $oldagent);
	}



	if ($timeout > 0)
		ini_set("default_socket_timeout", $oldtimeout);
	return $ctr;
}

function DoTest($test, $param, $hostname = "", $timeout = -1, $params = 0, $nodeid = "")
{
	global $NATS;
	if (!is_array($params)) {
		$params = array();
		for ($a = 0; $a < 10; $a++)
			$params[$a] = "";
	}

	switch ($test) {
		case "web":
		case "wsize":
			// Don't bother with pre-resolution as size only
			return WebTest($param, $timeout);
			break;
		/* -- modularised
		case "tcp": // nb TCP does not support timeouts currently
		$ip=ip_lookup($hostname);
		if ($ip=="0") return 0;
		$fp=@fsockopen($ip,$param);
		if ($fp<=0) return 0;
		@fclose($fp);
		return 1;
		break;
		*/
		case "wtime":
			$timer = new TFNTimer();
			// Do a pre-lookup
			$ip = url_lookup($param);
			if ($ip == "0")
				return -1; // dns lookup failed
			$timer->Start();
			$r = WebTest($param, $timeout);
			$elapsedTime = $timer->Stop();
			$elapsedTime = round($elapsedTime, 4);
			if ($r < 0)
				return -1; // open failed
			if ($r == 0)
				return -2; // no chars shown as returned
			if ($elapsedTime <= 0)
				return 0.0001;
			return $elapsedTime;
			break;

		case "host":
			$timer = new TFNTimer();
			if (preg_match("/[a-zA-Z]/", $param) > 0)
				$is_ip = false;
			else
				$is_ip = true;

			$timer->Start();
			if ($is_ip)
				$result = gethostbyaddr($param);
			else
				$result = gethostbyname($param);
			$elapsedTime = $timer->Stop();

			if ($result == $param) // lookup failed
				return -1;

			if ($result == "") // lookup failed
				return -1;

			$elapsedTime = round($elapsedTime, 4);
			if ($elapsedTime <= 0)
				return 0.0001;
			return $elapsedTime;
			break;

		case "testloop":
			return $param;
			break;

		case "testrand":
			mt_srand(microtime() * 1000000);
			if (($param == "") || ($param == 0))
				$param = 100;
			return mt_rand(0, $param);
			break;

		case "ping":
			return PingTest($param, $timeout);
			break;

		default:
			if (isset($NATS)) // try and see if a test is registered
			{
				if (isset($NATS->Tests->QuickList[$test])) // exists
				{
					$NATS->Tests->Tests[$test]->Create();
					return $NATS->Tests->Tests[$test]->instance->DoTest($test, $param, $hostname, $timeout, $params);
				}
			}

	}
	return -1; // did not run any test so untested
}

function SimpleEval($test, $result)
{
	global $NATS;
	switch ($test) {
		case "ping": // handles both types of simple evaluation (inbuilt ICMP and remote ping)
			if ($result <= 0)
				return 2;
			return 0;
		case "web":
		case "wsize":
			if ($result <= 0)
				return 2;
			return 0;
		/*
		case "tcp":
		if ($result==1) return 0;
		return 2;
		*/
		case "wtime":
			if ($result < 0)
				return 2;
			return 0;
		/*
		case "mysql":
		if ($result<=0) return 2;
		return 0;
		
		case "mysqlrows":
		if ($result<=0) return 2; // no rows returned or error
		return 0;
		*/
		case "host":
		case "dns":
			if ($result <= 0)
				return 2; // no records returned or error

		case "testloop":
			return 0;
		case "testrand":
			return 0;

		default:
			if (isset($NATS)) {
				if (isset($NATS->Tests->QuickList[$test])) {
					$NATS->Tests->Tests[$test]->Create();
					return $NATS->Tests->Tests[$test]->instance->Evaluate($result);
				}
			}
	}
	return -1; // untested if we don't know WTF the result was
}

function aText($al)
{
	return oText($al); // uses function in tests.inc.php with site config support	
/* -- depreciated
switch($al)
	{
	case -1: return "Untested";
	case 0: return "Passed";
	case 1: return "Warning";
	case 2: return "Failed";
	default: return "Unknown";
	}
*/
}
?>