<?php

// requires that the phpdns API already be included

// Params:
// 0 : FQDN URL
// 1 : DNS Server
// 2 : DNS UDP Delay

/* Method:
	Lookup FQDN hostname via UDP on DNS server
	If UDP lookup fails pause to total delay x seconds then try TCP
	Pre-resolve hostname DNS
	Perform URL open and fetch
	Close

Error values:
	-1 DNS Lookup Failed
	-2 URL Request Failed
	-3 Invalid DNS server
*/

global $NATS;

if (isset($NATS)) {
	class Page_Load_Sim_Test extends FreeNATS_Local_Test
	{
		function DoTest($testname, $param, $hostname = "", $timeout = -1, $params = false)
		{
			$timer = new TFNTimer();

			// First initialise DNS query object
			$dnsserver = $params[1];
			if ($dnsserver == "") return -3;
			$dnsserver = ip_lookup($dnsserver);

			$url = $param;

			$dns_delay = $params[2];
			if (($dns_delay == 0) || (!is_numeric($dns_delay))) $dns_delay = 0; // default no extra delay

			if ($timeout <= 0) $timeout = 60;

			$udp = true; // initial setting
			$port = 53;

			$dns_query = new DNSQuery($dnsserver, $port, $timeout, $udp, false); // run with debug off
			$type = "A";

			$matches = "";
			$out = preg_match("@^(?:http[s]*://)?([^/|\?|:]+)@i", $url, $matches);
			$hostname = $matches[1]; // strip out hostname for FQDN lookup

			$host_no_dots = str_replace(".", "", $hostname);
			if (is_numeric($host_no_dots)) $is_ip_address = true;
			else $is_ip_address = false;

			$timer->Start();

			if (!$is_ip_address) // only do the DNS lookup if the URL isn't an IP address already
			{
				$answer = $dns_query->Query($hostname, $type);
				//echo "DNS";

				if (($answer === false) || ($dns_query->error)) // query error
				{
					$udp = false; // switch to TCP
					$dns_query->udp = $udp;

					// wait!
					while ($timer->Stop() < $dns_delay) usleep(100);

					$answer = $dns_query->Query($hostname, $type);
					//echo "DNS2";
				}

				if ($answer->count <= 0) return -1; // no records returned
				if ($answer === false) return -1; // object is false
				if ($dns_query->error) return -1; // DNS object error

				$dns_time_taken = $timer->Stop();

				// if we get this far the DNS has worked

				$ip_address = url_lookup($url); // pre-cache DNS
			} else $dns_time_taken = 0;

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_HEADER, 1);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_MAXREDIRS, 32);
			if ($timeout > 0) curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			if ($timeout > 0) curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

			// restart timer
			$timer->Start();

			if (!$output = curl_exec($ch)) {
				$ctr = -1; // failed
			} else $ctr = round(curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD) / 1024, 2);

			$fetch_time_taken = $timer->Stop();

			curl_close($ch);

			if ($ctr <= 0) return -2; // URL request failed

			return $dns_time_taken + $fetch_time_taken; // return elapsed time taken

		}
		function Evaluate($result)
		{
			if ($result < 0) return 2; // failed
			return 0; // passed
		}
		function DisplayForm(&$row)
		{
			echo "<table border=0>";
			echo "<tr><td align=left>";
			echo "FQDN URL :";
			echo "</td><td align=left>";
			echo "<input type=text name=testparam size=30 maxlength=128 value=\"" . $row['testparam'] . "\">";
			echo "</td></tr>";
			echo "<tr><td colspan=2><i>Include http[s]://</i></td></tr>";
			echo "<tr><td align=left>";
			echo "Nameserver :";
			echo "</td><td align=left>";
			echo "<input type=text name=testparam1 size=30 maxlength=128 value=\"" . $row['testparam1'] . "\">";
			echo "</td></tr>";
			echo "<tr><td colspan=2><i>DNS Server to perform lookup on</i></td></tr>";
			echo "<tr><td align=left>";
			echo "DNS Delay :";
			echo "</td><td align=left>";
			echo "<input type=text name=testparam2 size=4 maxlength=4 value=\"" . $row['testparam2'] . "\">";
			echo "</td></tr>";
			echo "<tr><td colspan=2><i>If UDP DNS fails wait until elapsed time is x seconds before doing TCP lookup</i></td></tr>";
			echo "</table>";
		}
	}

	$params = array();
	$NATS->Tests->Register("loadsim", "Page_Load_Sim_Test", $params, "Page Load Simulator", 4, "Page Load Simulator");
	$NATS->Tests->SetUnits("loadsim", "Seconds", "s");
}
