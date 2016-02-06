<?php // freenats-node.php
// FreeNATS Push/Pull XML Node for Posix Environments
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

// Configuration Section

$nodeCfg=array();

$nodeCfg['allowpull']=true;
$nodeCfg['allowpush']=true;
$nodeCfg['nodekey']="";
$nodeCfg['nodeid']="";
$nodeCfg['restrict_pull_ip']="";
$nodeCfg['push_target']="";
$nodeCfg['tmp_dir']="/tmp/";

$nodeCfg['phpscan']=false;
$nodeCfg['phpscandir']="site/";

$nodeCfg['xmlscan']=false;
$nodeCfg['xmlscandir']="xml/";

$nodeCfg['version']="0.05";

$nodeCfg['uptime']	=	true;
$nodeCfg['disk']	=	true;
$nodeCfg['memory']	=	true;
$nodeCfg['net']		=	true;
$nodeCfg['systime']	=	true;
$nodeCfg['process']	=	true;

// End of Configuration Section

$configFile="config.inc.php";
$fileUpdate=true;

// XML Format
/*
Format:
<freenats-data>
 <header>
  <whatever>something</whatever>
 </header>
 <test name=testname>
  <name>testname</name>
  <desc>Test Description</desc>
  <value>return value</value>
  <alertlevel>suggested alert level</alertlevel>
 </test>
</freenats-data>
*/

class FreeNATS_XML_Node
{
var $xml="";
var $Config;

function AddLine($line)
{
$this->xml.=$line."\n";
}

function AddTest($name,$desc,$val,$lvl=-1)
{
$this->AddLine("");
$this->AddLine(" <test NAME=\"".$name."\">");
$this->AddLine("  <name>".$name."</name>");
$this->AddLine("  <desc>".$desc."</desc>");
$this->AddLine("  <value>".$val."</value>");
$this->AddLine("  <alertlevel>".$lvl."</alertlevel>");
$this->AddLine(" </test>");
$this->AddLine("");
}

function FreeNATS_XML_Node()
{
global $nodeCfg;
$this->AddLine("<?xml version=\"1.0\" encoding=\"UTF-8\"?>");
$this->Config=&$nodeCfg;
}

function Start()
{
$this->AddLine("<freenats-data>");
}

function Stop()
{
$this->AddLine("</freenats-data>");
}

function DataHeader($is_header=true)
{
if ($is_header) $this->AddLine(" <header>");
else $this->AddLine(" </header>");
}

function HeaderItem($name,$value)
{
$this->AddLine("  <".$name.">".$value."</".$name.">");
}

function ScreenOutput()
{
header("Content-type: text/xml");
echo $this->xml;
exit();
}

}

$Node=new FreeNATS_XML_Node();
$Node->Start();


// How Were We Called
$pull=false;

if (isset($_SERVER['REMOTE_ADDR'])) // called via HTTP
	{
	$pull=true;
	if ($nodeCfg['restrict_pull_ip']!="") // check IP
		{
		if ($_SERVER['REMOTE_ADDR']!=$nodeCfg['restrict_pull_ip'])
			{
			echo "Authorisation Failure: IP Address Denied";
			exit();
			}
		}
	if ($nodeCfg['nodekey']!="") // check authorisation
		{
		if ( !(isset($_REQUEST['nodekey'])) || ($_REQUEST['nodekey']!=$nodeCfg['nodekey']) )
			{
			echo "Authorisation Failure: Incorrect NODEKEY Configured";
			exit();
			}
		}
	if (isset($_REQUEST['noupdate']) && ($_REQUEST['noupdate']==1)) $fileUpdate=false;
	}	
else // called via CLI
	{
	$a=1;
	for ($a=1; $a<$argc; $a++)
		{
		switch($argv[$a])
			{
			case "-c": case "--c": case "-config": case "--config":
			 $configFile=$argv[++$a];
			 break;
			case "-d": case "--d": case "-debug": case "--debug":
			 $pull=true; // output to the console
			 break;
			case "--noupdate": case "-noupdate":
			 $fileUpdate=false;
			 break;
		 	}
	 	}
 	}
 	
if ($configFile!="") require($configFile);


$Node->DataHeader();
$Node->HeaderItem("name","FreeNATS Posix Node XML");
$Node->HeaderItem("version",$nodeCfg['version']);
if (!$pull) // we are pushing instead
	$Node->HeaderItem("nodekey",$nodeCfg['nodekey']);
$Node->DataHeader(false);

// Node Type and Version Pseudo-Tests
$Node->AddTest("fnn.version","FreeNATS Node Version",$nodeCfg['version'],0);
$Node->AddTest("fnn.name","FreeNATS Node Type","Posix XML",0);

//$Node->AddTest("bob.one","Bob One",10,0);
//$Node->AddTest("bob.two","Bob Two",11,0);


// Data from Uptime
if ($nodeCfg['uptime'])
{
$uptime=exec("/usr/bin/uptime");

$ut=preg_split("/\s+/",$uptime);
//var_dump($ut);



$Node->AddTest("uptime.1m","One Minute Load Average",substr($ut[count($ut)-3],0,strlen($ut[count($ut)-3])-1),0);
$Node->AddTest("uptime.5m","Five Minute Load Average",substr($ut[count($ut)-2],0,strlen($ut[count($ut)-2])-1),0);
$Node->AddTest("uptime.15m","Fifteen Minute Load Average",$ut[count($ut)-1],0);
$Node->AddTest("uptime.users","Logged in Users",$ut[count($ut)-7],0);
}

// Data from Time
if ($nodeCfg['systime'])
{
$nowx=time();
$utf=date("Y-m-d H:i:s",$nowx);
$dts=date("H:i:s d/m/Y",$nowx);
$pasthour= (date("i",$nowx)*60)+date("s",$nowx);

$Node->AddTest("systime.x","Node Time (Seconds Since Epoch)",$nowx,0);
$Node->AddTest("systime.utf","Node Time (UTF)",$utf,0);
$Node->AddTest("systime.dts","Note Time",$dts,0);
$Node->AddTest("systime.sph","Seconds Past Hour",$pasthour,0);
}

// ------------------ DISK SPACE

// Data from DF
if ($nodeCfg['disk'])
{
 $result=array();
  exec("/bin/df -P",$result);
 
  // filesystem blocks used available use% mount
  for ($a=1; $a<count($result); $a++)
   {
   $parts=preg_split("/\s+/",$result[$a]);
   if (count($parts)>4) // not a duff line
   		{
		$filesystem=$parts[0];
		$size=$parts[1]/1024;
		$used=$parts[2]/1024;
		$free=$parts[3]/1024;
	   	$perc=substr($parts[4],0,(strlen($parts[4])-1));
		$percfree=100-$perc;
	   	$mount=$parts[5];
		
		if ($perc >= 90) $alertlevel=2; // failed
		else if ($perc >= 80) $alertlevel=1; // warning
		else $alertlevel=0; // passed
	  
	   //$nicefs=str_replace("/","_",$filesystem);
	   $name=$filesystem.".size";
	   $desc="Total Size of ".$filesystem." (".$mount.") (Mb)";
	   $Node->AddTest($name,$desc,round($size,2),$alertlevel);
	   
	   $name=$filesystem.".used";
	   $desc="Space Used on ".$filesystem." (".$mount.") (Mb)";
	   $Node->AddTest($name,$desc,round($used,2),$alertlevel);
	   
	   $name=$filesystem.".free";
	   $desc="Space Free on ".$filesystem." (".$mount.") (Mb)";
	   $Node->AddTest($name,$desc,round($free,2),$alertlevel);
	   
	   $name=$filesystem.".perc";
	   $desc="Percentage Used on ".$filesystem." (".$mount.")";
	   $Node->AddTest($name,$desc,$perc,$alertlevel);
	
	   $name=$filesystem.".percfree";
	   $desc="Percentage Free on ".$filesystem." (".$mount.")";
	   $Node->AddTest($name,$desc,$percfree,$alertlevel);
		}
   }
}
	


// ------------------ RAM
// Data from FREE
if ($nodeCfg['memory'])
{

 
  $free=array();
  exec("/usr/bin/free",$free);
  
  //unset($this->mm_elements);

  for ($fc=1; $fc< count($free); $fc++)
   {
   // Mem: Swap:   -- total, used, free -- kb
   $parts=preg_split("/\s+/",$free[$fc]);
   $proc=false;
   if ($parts[0]=="Mem:") { $proc=true; $type="System Memory"; $prefix="mem"; }
   else if ($parts[0]=="Swap:") { $proc=true; $type="System Swap File"; $prefix="swap"; }
   /*
   else 
   	{
	   echo $free[$fc]."\n";
	   exit();
  }*/
   
   if ($proc)
    {
	$total=round($parts[1]/1024,3);
	$usedmb=round($parts[2]/1024,3);
	$freemb=round($parts[3]/1024,3);
	$used_perc=0;
	$free_perc=0;
	if ($total>0)
		{
		if ($used>0) $used_perc=round (($usedmb/$total)*100,2);
		if ($free>0) $free_perc=round (($freemb/$total)*100,2);
		}
	$name=$prefix.".total";
	$Node->AddTest($name,$type." Total (Mb)",$total,0);

		
	$name=$prefix.".used"; // parts[2] used kb
	$Node->AddTest($name,$type." Used (Mb)",$usedmb,0);
		
	$name=$prefix.".free"; // parts[3] free kb
	$Node->AddTest($name,$type." Free (Mb)",$freemb,0);
	
	$name=$prefix.".free.perc";
	$Node->AddTest($name,$type." Free (%)",$free_perc,0);
	
	$name=$prefix.".used.perc";
	$Node->AddTest($name,$type." Used (%)",$used_perc,0);
    } // end of if $proc
    
   } // end of for
 
}



// ------------------ NETWORK USAGE
// Data from /proc/net/dev
if ($nodeCfg['net'])
{
$netarr=@file("/proc/net/dev");
for($a=2; $a<count($netarr); $a++)
	{
	$line=explode(":",$netarr[$a]);
	$dev=trim($line[0]);
	$data=trim($line[1]);
	$darr=preg_split("/[\s]+/",$data);
	//print_r($darr);
	//exit();
	$rx=trim($darr[0]);
	$tx=trim($darr[8]);
	if ($rx=="") $rx=0; // bodge
	if ($tx=="") $tx=0;
	$tt=$tx+$rx;
	
	$Node->AddTest("net.".$dev.".rxt","Total Received on Interface ".$dev." (bytes)",$rx,0);
	$Node->AddTest("net.".$dev.".txt","Total Sent on Interface ".$dev." (bytes)",$tx,0);
	$Node->AddTest("net.".$dev.".trt","Total Passed on Interface ".$dev." (bytes)",$tt,0);
	
	$trrx=0;
	$trtx=0;
	$trtt=0;
	$trlvl=0;
	
	$nowx=time();
	// does the file exist
	$fp=fopen($nodeCfg['tmp_dir']."fnnode.net.".$dev,"r");
	if ($fp>0) // yes
		{
		$lastx=trim(fgets($fp,128));
		$lrx=trim(fgets($fp,128));
		$ltx=trim(fgets($fp,128));
		$ltt=$lrx+$ltx;
		// wrap checking and the like...
		if ( ($lrx>$rx) ) //|| ($ltx>$tx) || ($ltt>$tt) )
			{
			$trlvl=-1; // untested
			$diffx=0;
			//echo "untested ".trim($lrx)."-$rx";
			}
		else // test it
			{
			$diffx=$nowx-$lastx;
			if ($diffx>0)
				{
				$trrx=(($rx-$lrx)/$diffx)/1024;
				$trtx=(($tx-$ltx)/$diffx)/1024;
				$trtt=(($tt-$ltt)/$diffx)/1024;
				}
			if($trrx=="") $trrx=0;
			if($trtx=="") $trtx=0;
			if($trtt=="") $trtt=0;
			}
		}
	else $trlvl=-1;
	@fclose($fp);
	
	// write my file
	if ($fileUpdate)
		{
		//echo "Writing Files!\n";
		$fp=fopen($nodeCfg['tmp_dir']."fnnode.net.".$dev,"w");
		fputs($fp,$nowx."\n");
		fputs($fp,$rx."\n");
		fputs($fp,$tx."\n");
		fclose($fp);
		}
		
	$Node->AddTest("net.".$dev.".rx","Receive Speed on ".$dev." (kbyte/s)",$trrx,$trlvl);
	$Node->AddTest("net.".$dev.".tx","Transmit Speed on ".$dev." (kbyte/s)",$trtx,$trlvl);
	$Node->AddTest("net.".$dev,"Combined Speed on ".$dev." (kbyte/s)",$trtt,$trlvl);
	$Node->AddTest("net.".$dev.".elapsed","Speed Sample Time on ".$dev." (secs)",$diffx,$trlvl);
	}
}
	
// ------------------ PROCESS INFORMATION
if ($nodeCfg['process'])
	{
	$ps=array();
	exec("/bin/ps -e -w",$ps);
	
	$pdata=array();
	//foreach($ps as $psl)
	for ($z=1; $z<count($ps); $z++)
		{
		$psl=$ps[$z];
		$lineparts=preg_split("/\s+/",$psl);
		$parts=array();
		for ($a=0; $a<count($lineparts); $a++)
			{
			if ($lineparts[$a]!="") $parts[]=$lineparts[$a];
			}
		//echo $psl."\n";
		// pid pts time name
		//echo "[".$parts[0]."] ";
		//echo "[".$parts[1]."] ";
		//echo "[".$parts[2]."] ";
		//echo "[".$parts[3]."]\n";
		
		$pname=$parts[3];
		if (isset($pdata[$pname])) $pdata[$pname]++;
		else $pdata[$pname]=1;
		}
	
	foreach($pdata as $key=>$val)
		{
		$Node->AddTest("ps.".$key,"Number of ".$key." Processes",$val,0);
		}
	}
	

// ------------------ END OF TEST DATA


// End FreeNATS Data
$Node->Stop();

if ($pull)
	{
	// Output Data to Screen
	$Node->ScreenOutput();
	}
else // push
	{
	// PHP 5 Required
	$data=array( "nodeid" => $nodeCfg['nodeid'],
		"nodekey" => $nodeCfg['nodekey'],
		"xml" => $Node->xml );
	$data=http_build_query($data);
	
	$request=array( "http" => array( "method" => "POST",
		"header" => "Content-type: application/x-www-form-urlencoded", "content" => $data ) );
	$context=stream_context_create($request);
	$fp=fopen($nodeCfg['push_target'],'rb',false,$context);
	if ($fp<=0)
		{
		echo "Push Failed to open URL\n";
		exit();
		}
	$serverdata=@stream_get_contents($fp);
	
	if ($serverdata===false)
		{
		echo "Push Data is FALSE\n";
		exit();
		}
		
	if ($serverdata!="1")
		{
		echo "Server Returned Error: ".$serverdata."\n";
		exit();
		}
		
	echo "Push Succeeded\n";
	}
		
?>
