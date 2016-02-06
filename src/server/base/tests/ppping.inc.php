<?php
/* -------------------------------------------------------------
This file is part of PurplePixie Ping (PPPing)

PPPing is (C) Copyright 2010 PurplePixie Systems

PPPing is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

PPPing is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with PPPing.  If not, see www.gnu.org/licenses

For more information see www.purplepixie.org/phpping
-------------------------------------------------------------- */

/**
 * PPPing PHP Ping Utility
 * @package PPPing PHP Ping Utility
 * @author David Cutting
 * @version 0.01
**/

/**
 * Main PPPing Class
 * @package PPPing PHP Ping Utility
**/
class PPPing
{
/**
 * Time-to-Live for IP Packet (DO NOT USE)
 *
 * -1 Uses system default (usually 64). Please note that this is currently
 * not functional.
**/
var $ttl=-1;

/**
 * Hostname to ping (resolvable host or IP address)
**/
var $hostname="";

/**
 * Identifier - will fill with random content (16 bits)
**/
var $identity=0;

/**
 * Sequence number in decimal (16 bits)
**/
var $sequence=0;

/**
 * Timeout in seconds - maximum wait for a response before timeout
**/
var $timeout=10;

/**
 * Timer start seconds
**/
var $timer_start_sec=0;

/**
 * Timer start mseconds
**/
var $timer_start_msec=0;

/**
 * Data package for the ping
**/
var $data_package = "PPPing";

/**
 * Debug - prints output to the screen
**/
var $debug=false;

/**
 * Holds information on last result
**/
var $Last = array();

/**
 * Clears last data
**/
function clearLast()
	{
	$this->last = array(
		"set" => false,
		"result" => 0,
		"ttl" => 0,
		"hops" => 0,
		"source" => "",
		"destination" => "" );
	}
/**
 * Get a padded hex identifier
**/
function getIdentity()
	{
	if ( (is_numeric($this->identity)) && ($this->identity>=0) && ($this->identity<65535) )
		$id=$this->identity;
	else $id=0;
	
	$id=dechex($id);
	$id=str_pad($id,4,"0",STR_PAD_LEFT);
	$id=pack("H*",$id);
	
	return $id;
	}

/**
 * Get a padded hex sequence
**/
function getSequence()
	{
	if ( (is_numeric($this->sequence)) && ($this->sequence>=0) && ($this->sequence<65535) )
		$seq=$this->sequence;
	else $seq=0;
	$seq=dechex($seq);
	$seq=str_pad($seq,4,"0",STR_PAD_LEFT);
	$seq=pack("H*",$seq);
	
	return $seq;
	}
	
/**
 * Returns a hex string of the binary data for debug purposes
**/
function getHex($data)
	{
	$parts=unpack("H*",$data);
	return $parts[1];
	}
	
/**
 * Randomise identity and/or sequence within 16 bit parameters
**/
function Randomise($identity=true,$sequence=false)
	{
	mt_srand(microtime()*1000000);
	if ($identity) $this->identity=mt_rand(0,65534);
	if ($sequence) $this->sequence=mt_rand(0,65534);
	}
	
/**
 * Start timer (reset values)
**/
function startTimer()
	{
	$now=microtime();
	$timearray=explode(" ",$now);
	$this->timer_start_sec=$timearray[1];
	$this->timer_start_msec=$timearray[0];
	}
	
/**
 * Stop timer (return result)
**/
function stopTimer()
	{
	$now=microtime();
	$timearray=explode(" ",$now);
	
	$finish_secs=$timearray[1];
	$finish_msecs=$timearray[0];
	
	$elapsed_seconds = $finish_secs - $this->timer_start_sec;
	$elapsed_time = $elapsed_seconds + $finish_msecs - $this->timer_start_msec;
	
	$elapsed_ms = $elapsed_time * 1000;
	
	$elapsed_ms = round($elapsed_ms,3);
	
	return $elapsed_ms;
	}
	
	
/**
 * Constructor - randomises ID
**/
function PPPing()
	{
	$this->Randomise();
	}
	
/**
 * Returns a dotted quad from hex format IPv4 address
**/
function ipAddress($hexip)
	{
	$quad="";
	for($a=0; $a<=6; $a+=2)
		{
		$portion=substr($hexip,$a,2);
		$decimal=hexdec($portion);
		if ($quad!="") $quad.=".";
		$quad.=$decimal;
		}
	return $quad;
	}
	
/**
 * Generate an ICMP checksum
**/
function Checksum($data)
    {
    if (strlen($data)%2)
		$data .= "\x00";
    
    $bit = unpack('n*', $data);
    $sum = array_sum($bit);
    
    while ($sum >> 16)
    $sum = ($sum >> 16) + ($sum & 0xffff);
    
    return pack('n*', ~$sum);
    }

/**
 * Do a ping of the set hostname
 *
 * @return float
 * Returns a negative number of failure which can be turned into text with
 * the strError method. A positive number is a response in milliseconds (ms)
**/
function Ping()
	{
	$this->clearLast();
    $type = "\x08"; // icmp echo
    $code = "\x00"; 
    $checksum = "\x00\x00"; // initial
    $identifier = $this->getIdentity();
	$dec_identity = $this->identity;
	//$identifier = "\x00\x00";
	//$seqNumber = "\x00\x00";
    $seqNumber = $this->getSequence();
	$dec_sequence = $this->sequence;
    $data = $this->data_package;
    $package = $type.$code.$checksum.$identifier.$seqNumber.$data;
    $checksum = $this->Checksum($package); // proper checksum
    $package = $type.$code.$checksum.$identifier.$seqNumber.$data;
	
	$ip_protocol_code = getprotobyname("ip");
	$ip_ttl_code = 7;
    
	// Lookup hostname
	$ips=str_replace(".","",$this->hostname);
	if (!is_numeric($ips))
		{
		$host=gethostbyname($this->hostname);
		if ($host==$this->hostname) return -5;
		}
	else $host=$this->hostname;
    
    // Create Socket
    $socket = socket_create(AF_INET, SOCK_RAW, 1); // @
    	//or die(socket_strerror(socket_last_error()));
    if (!$socket) return -3;
    
    // Set Non-Blocking
    socket_set_nonblock($socket); // @
	
	$socket_ttl = socket_get_option($socket,$ip_protocol_code,$ip_ttl_code);
	
	//for ($a=0; $a<64; $a++)
	//	echo $a." - ".@socket_get_option($socket,$ip_protocol_code,$a)."\n";
	
	if ($this->ttl>0)
		{
		socket_set_option($socket,$ip_protocol_code,$ip_ttl_code,128);
		$socket_ttl = socket_get_option($socket,$ip_protocol_code,$ip_ttl_code);
		//socket_set_option($socket,Socket::IPPROTO_IP,Socket::IP_TTL,128);
		//$socket_ttl = socket_get_option($socket,Socket::IPPROTO_IP,Socket::IP_TTL);
		
		}
	else $socket_ttl = 64; // standard TTL
		
    	
    // Connect Socket
    $sconn=socket_connect($socket, $host, null); // @
    if (!$sconn) return 0;
    
	// Package Size
	//$package_size = 8+strlen($data);
	$package_size = strlen($package);
	
    // Send Data
    socket_send($socket, $package, $package_size, 0); // @
        
    // Start Timer
    $this->startTimer();
    $startTime = microtime(true); // need this for the looping section
    

    // Read Data
    $keepon=true;

    while( (false===($echo_reply=socket_read($socket, 255))) && $keepon) // @socket_read
    	{ 
    	
    	if ( (microtime(true) - $startTime) > $this->timeout )
    		$keepon=false;
			
		}
    	
		
	if ($keepon) // didn't time out - read data
    	{
	    $elapsed=$this->stopTimer();
		
		socket_close($socket); // @
		
		if ( $echo_reply === false ) return -4;
		else if (strlen($echo_reply)<2) return -2;
	
		$rx_parts = unpack("C*",$echo_reply);
		$tx_parts = unpack("C*",$package);
		$ipheader="";
		$ipheader_hex="";
		
		if ($rx_parts[1] == 0x45) // IP Header Information
			{
			$ipheader=substr($echo_reply,0,20);
			$ipheader_hex = $this->getHex($ipheader);
			$echo_reply=substr($echo_reply,20);
			$rx_parts = unpack("C*",$echo_reply);
			}
			
		if ($this->debug)
			{
			echo "\n";
			echo "    TyCoChksIdenSequData\n";
			echo "TX: ".$this->getHex($package)."\n";
			echo "RX: ".$this->getHex($echo_reply)."\n";
			if ($ipheader!="") echo "HR: ".$ipheader_hex."\n";
			}
			
		$echo_reply_hex = $this->getHex($echo_reply);
		$reply_type = $rx_parts[1];
		$reply_code = $rx_parts[2];
		$reply_identity = hexdec(substr($echo_reply_hex,8,4));
		$reply_sequence = hexdec(substr($echo_reply_hex,12,4));
			
		$match=true;
		if ($ipheader!="")
			{
			$source=substr($ipheader_hex,24,8);
			$dest=substr($ipheader_hex,32,8);
			$ttl=hexdec(substr($ipheader_hex,16,2));
			if ($this->debug) echo $this->ipAddress($source)." => ".$this->ipAddress($dest)." | ttl: ".$ttl."\n";
			if ($source==$dest) $match=true;
			else $match=false;
			
			$this->last["set"]=true;
			$this->last["source"]=$this->ipAddress($source);
			$this->last["destination"]=$this->ipAddress($dest);
			$this->last["ttl"]=$ttl;
			$this->last["hops"]=$socket_ttl - $ttl;
			
			
			}
	
		if ( (($rx_parts[1]==0) || (($rx_parts[1]==8)&&($match))) && ($rx_parts[2]==0) )
			{ // is echo_reply (0) or is echo_request (8) AND match (from same host)
			  // and has code of 0
			// valid response
			if ($reply_identity != $dec_identity) return -8; // ID mismatch
			else if ($reply_sequence != $dec_sequence) return -7; // sequence mismatch
			else
				{
				$this->last["result"]=$elapsed;
				return $elapsed;
				}
			}
		else
			{ // ICMP Error
			return -9;
			}
	
		}
    socket_close($socket); // @
    return -1; // timeout
	}
	
/**
 * Returns textual error for code
**/
function strError($code)
	{
	switch($code)
		{
		case -1: return "Timed Out"; break;
		case -2: return "Reply Too Short"; break;
		case -3: return "Failed to Open Socket"; break;
		case -4: return "Invalid (false) Response"; break;
		case -5: return "Hostname Lookup Failed"; break;
		case -7: return "Sequence Mismatch"; break;
		case -8: return "Identity Mismatch"; break;
		case -9: return "ICMP Generic Error"; break;
		
		default: return "Unknown Error"; break;
		}
	}


}

?>