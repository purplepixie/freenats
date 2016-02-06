<?php
/* -------------------------------------------------------------
This file is part of FreeNATS

FreeNATS is (C) Copyright 2008 PurplePixie Systems

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

$ranges=array();
$actranges=array();
$rangecount=0;
$recorded=0;
$webprobe=false;
$live=false;
$debug=false;
$outfile="discover.xml";
$output="";



function AddNode($nodeid,$hostname,$description="")
{
global $output;
$output.="<node NODEID=\"".$nodeid."\">\n";
$output.=" <nodeid>".$nodeid."</nodeid>\n";
$output.=" <hostname>".$hostname."</hostname>\n";
$output.=" <nodedesc>".$description."</nodedesc>\n";
$output.="</node>\n\n";
}

function AddLocaltest($nodeid,$testtype,$param)
{
global $output,$recorded;
$output.="<localtest>\n";
$output.=" <nodeid>".$nodeid."</nodeid>\n";
$output.=" <testtype>".$testtype."</testtype>\n";
$output.=" <testparam>".$param."</testparam>\n";
$output.="</localtest>\n\n";
}

 function DiscovericmpChecksum($data)
    {
    if (strlen($data)%2)
    $data .= "\x00";
    
    $bit = unpack('n*', $data);
    $sum = array_sum($bit);
    
    while ($sum >> 16)
    $sum = ($sum >> 16) + ($sum & 0xffff);
    
    return pack('n*', ~$sum);
    }
   
function DiscoverPing($host)
	{
    // Make Package
    $type= "\x08";
    $code= "\x00";
    $checksum= "\x00\x00";
    $identifier = "\x00\x00";
    $seqNumber = "\x00\x00";
    $data= "FreeNATS";
    $package = $type.$code.$checksum.$identifier.$seqNumber.$data;
    $checksum = DiscovericmpChecksum($package); // Calculate the checksum
    $package = $type.$code.$checksum.$identifier.$seqNumber.$data;
    
    // Return s or ms(s*1000)
    $returnsecs=true;
    

	// Timeout Values
    $timeout=10;
    
    // Create Socket
    $socket = @socket_create(AF_INET, SOCK_RAW, 1);
    	//or die(socket_strerror(socket_last_error()));
    if (!$socket) return -1;
    
    // Set Non-Blocking
    @socket_set_nonblock($socket);
    	
    // Connect Socket
    $sconn=@socket_connect($socket, $host, null);
    if (!$sconn) return -1;
    
    // Send Data
    @socket_send($socket, $package, strLen($package), 0);
        
    $startTime=microtime(true);
    

    // Read Data
    $keepon=true;

    while( (!(@socket_read($socket, 255))) && $keepon)
    	{ // basically just kill time
    	// consider putting some sort of sleepy thing here to lower load but would f* with figures!
    	
    	if ( (microtime(true) - $startTime) > $timeout )
    		$keepon=false;
		}
    	
	if ($keepon) // didn't time out - read data
    	{
	 
	    @socket_close($socket);
    	
    	return 1;
    	
    	}
    	
    // Socket timed out
    @socket_close($socket);
    return 0;
	}


function ddt($txt) // discovery debug text
{
global $debug;
if ($debug) echo $txt."\n";
}

if ($argc<2)
	{
	echo "FreeNATS Discovery Tool\n";
	echo "Usage: php discover.php <IP/RANGE> [<IP/RANGE> ...] [options]\n\n";
	echo "Goes through IPs or ranges and discovers nodes, outputting an XML file for\n";
	echo "import to FreeNATS with the bulk importer.\n\n";
	echo "IPs or Ranges are Specified as:";
	echo " 10.0.10.1 - single IP address\n";
	echo " 10.0.10.1-10.0.10.254 - range of IP addresses\n";
	echo " 10.0.10.0/24 - network with numeric netmask\n";
	echo " 10.0.10.0/255.255.255.0 - network with IPv4 netmask\n\n";
	echo "Options are:";
	echo " --file <filename> - output XML file (defaults to discover.xml)\n";
	echo " --webprobe - do a web probe and add the test if found\n";
	echo " --recorded - set the record data flag for any discovered tests by default\n\n";
	echo "See www.purplepixie.org/freenats for more information.\n\n";
	exit();
	}

for ($ac=1; $ac<$argc; $ac++)
	{
	$arg=$argv[$ac];
	if ($arg=="--live") $live=true;
	else if ($arg=="--debug") $debug=true;
	else if ($arg=="--file") $outfile=$argv[++$ac];
	else if ($arg=="--webprobe") $webprobe=true;
	else if ($arg=="--recorded") $recorded=1;
	else 
		{
		$ranges[$rangecount]=$arg;
		$actranges[$rangecount]['start']=0;
		$actranges[$rangecount]['finish']=0;
		
		if (strpos($arg,"-")!==false) // range
			{
			$pos=strpos($arg,"-");
			$from=substr($arg,0,$pos);
			$to=substr($arg,$pos+1);
			$actranges[$rangecount]['start']=ip2long($from);
			$actranges[$rangecount]['finish']=ip2long($to);
			}
		else if (strpos($arg,"/")!==false) // mask delimited
			{
			$pos=strpos($arg,"/");
			$ip=substr($arg,0,$pos);
			$mask=substr($arg,$pos+1);
			if (is_numeric($mask)) // numeric mask
				{
				ddt("Numeric Netmask ".$mask);
				$netmask=0;
				for ($a=0; $a<$mask; $a++)
					{
					$val=pow(2,(32-$a-1));
					ddt("Numeric Netmask: Pos ".$a." Adding ".$val);
					$netmask+=$val;
					}
				ddt("Numeric Netmask ".long2ip($netmask)." ".$netmask);
				$netmask=ip2long(long2ip($netmask)); // weirdness avoidance!!
				//
				}
			else $netmask=ip2long($mask);
			ddt("Netmask ".$netmask);
			$ipaddr=ip2long($ip);
			ddt("IP ".$ipaddr." (".$ip.")");
			$network=($ipaddr & $netmask);
			$firsthost=$network+1;
			
			$broadcast=($network | (~$netmask));
			$lasthost=$broadcast-1;
			
			$actranges[$rangecount]['start']=$firsthost;
			
			$actranges[$rangecount]['finish']=$lasthost;
			
			}
		else
			{ // single host
			$actranges[$rangecount]['start']=ip2long($arg);
			$actranges[$rangecount]['finish']=ip2long($arg);
			}
		
		$rangecount++;
		}
	}
	

$output.="<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
$output.="<freenats-data>\n\n";

$output.="<default TYPE=\"node\">\n";
$output.=" <nodeenabled>1</nodeenabled>\n";
$output.=" <pingtest>0</pingtest>\n";
$output.="</default>\n\n";

$output.="<default TYPE=\"localtest\">\n";
$output.=" <testenabled>1</testenabled>\n";
$output.=" <testrecord>".$recorded."</testrecord>\n";
$output.="</default>\n\n";

	

echo "FreeNATS Discover Started\n";
echo " Live: ";
if ($live) echo "Yes";
else echo "No";
echo "    Debug: ";
if ($debug) echo "Yes";
else echo "No";
echo "\n File: ".$outfile;
echo "\n\n";
echo "Ranges: ".$rangecount."\n";
if ($rangecount<=0)
	{
	echo "\nNo ranges or IP addresses specified\n";
	}
	
for($a=0; $a<$rangecount; $a++)
 {
 echo " ".$ranges[$a]." ".$actranges[$a]['start']."-".$actranges[$a]['finish']." ".long2ip($actranges[$a]['start'])."-".long2ip($actranges[$a]['finish'])."\n";
 }
echo "\n";

for($a=0; $a<$rangecount; $a++)
	{
	$start=$actranges[$a]['start'];
	$finish=$actranges[$a]['finish'];
	echo "+ ".long2ip($start)." - ".long2ip($finish)."\n";
	for ($ip=$start; $ip<=$finish; $ip++)
		{
		ddt("- ".long2ip($ip)." (".$ip.")");
		$res=DiscoverPing(long2ip($ip));
		if ($res<0)
			{
			echo "Fatal Error: Could Not Open ICMP Connection (Ping Send Failed)\n\n";
			exit();
			}
		else if ($res==0)
			{
			ddt("- No Reply for Ping");
			}
		else
			{
			echo "- Ping Returned - Host Active\n";
			$hostname=gethostbyaddr(long2ip($ip));
			echo "- Name: ".$hostname."\n";
			$ipaddr=long2ip($ip);
			if ($hostname==$ipaddr)
				{
				$nodeid=$ipaddr;
				}
			else
				{
				$exp=explode(".",$hostname);
				$nodeid=$exp[0];
				}
			echo "- NodeID: ".$nodeid."\n";
			AddNode($nodeid,$ipaddr,$hostname);
			if ($webprobe)
				{
				$url="http://".$ipaddr."/";
				echo "- Web Probe: ".$url."... ";
				$fp=@fopen($url,"r");
				if ($fp<=0) echo "Failed\n";
				else
					{
					echo "Succeeded\n";
					fclose($fp);
					AddLocaltest($nodeid,"webtime",$url);
					echo "- Adding Web Test ".$url."\n";
					}
				}
			}
		echo "\n";
		}
	echo "\n";
	}
	
$output.="\n</freenats-data>";

echo "\nFinish... Writing File... ";
$fp=fopen($outfile,"w");
fputs($fp,$output,strlen($output));
fclose($fp);
echo "\n";

?>