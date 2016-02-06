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

ob_start();
require("include.php");
$NATS->Start();
$session=true;
if (!$NATS_Session->Check($NATS->DB))
	{
	$session=false;
	}

$abs=GetAbsolute();
	
if (isset($_REQUEST['mode'])) $mode=$_REQUEST['mode'];
else $mode="xml";

// api.public - is available without session auth
// api.key - usage key used if public and no session (if set)

if ($NATS->Cfg->Get("api.public",0)!=1) // NOT public
	{
	if (!$session)
		{
		echo "Error: Public API Access Disabled";
		exit();
		}
	}
else if (!$session) // IS PUBLIC and not logged in
	{
	$key=$NATS->Cfg->Get("api.key","");
	if ($key!="") // require a key
		{
		if ( (!isset($_REQUEST['apikey'])) || ($_REQUEST['apikey'] != $key) )
			{
			// No key or doesn't match
			echo "Error: Public API Key Mismatch";
			exit();
			}
		}
	}
	
// Got this far so it must be a winner (either public or no key or correct key)



function lo($text) // line out
{
echo $text."\n";
}

// Header
ob_clean();
switch($mode)
	{
	case "xml": 
		header("Content-type: text/xml");
		lo("<?xml version=\"1.0\" encoding=\"UTF-8\" ?>");
		lo("<freenats-data>");
	break;
	
	case "js":
	if (isset($_REQUEST['dataid'])) $dataid=$_REQUEST['dataid'];
	else
		{
		$allow="abcdef0123456789";
		$allow_len=strlen($allow);
		mt_srand(microtime()*1000000);
		$id_len=10;
		$dataid="fnd_";
		for ($a=0; $a<$id_len; $a++)
			{
			$dataid.=$allow[mt_rand(0,$allow_len-1)];
			}
		}
	lo("var ".$dataid."=new Array();");
	break;
	
	
	}
ob_end_flush();
	
// Queries
$query_count=count($_REQUEST['query']);
for ($a=0; $a<$query_count; $a++)
	{
	switch($_REQUEST['query'][$a])
		{
		case "nodelist":
		$q="SELECT nodeid FROM fnnode";
		if ((!isset($_REQUEST['param'][$a])) || ($_REQUEST['param'][$a]!=1)) $q.=" WHERE nodeenabled=1";
		$q.=" ORDER BY weight ASC";
		$r=$NATS->DB->Query($q);
		if ($mode=="js")
			{
			lo($dataid."[".$a."]=new Array();");
			}
		else if ($mode=="xml") lo("<nodelist count=\"".$NATS->DB->Num_Rows($r)."\" query=\"".$a."\">");
		$ctr=0;
		while ($row=$NATS->DB->Fetch_Array($r))
			{
			$nodealert=$NATS->NodeAlertLevel($row['nodeid']);
			if ($mode=="xml") lo("<node nodeid=\"".$row['nodeid']."\" alertlevel=\"".$nodealert."\">".$row['nodeid']."</node>");
			else if ($mode=="js")
				{
				lo($dataid."[".$a."][".$ctr."]=new Array();");
				lo($dataid."[".$a."][".$ctr."][0]='".$row['nodeid']."';");
				lo($dataid."[".$a."][".$ctr."][1]='".$nodealert."';");
				}
			$ctr++;
			}
		if ($mode=="xml") lo("</nodelist>");
		$NATS->DB->Free($r);
		break;
			
			
		case "node":
		$nodedata=$NATS->GetNode($_REQUEST['param'][$a]);
		if ($nodedata) // got a valid response
			{
			if ($mode=="js")
				{
				lo($dataid."[".$a."]=new Array();");
				}
			else if ($mode=="xml") lo("<node nodeid=\"".$nodedata['nodeid']."\" query=\"".$a."\">");
			$ctr=0;
			foreach($nodedata as $key => $val)
				{
				if (!is_numeric($key)) // pesky double-arrays avoided
					{
					if ($mode=="xml") lo(" <".$key.">".$val."</".$key.">");
					else if ($mode=="js")
						{
						lo($dataid."[".$a."][".$ctr."]=new Array;");
						lo($dataid."[".$a."][".$ctr."][0]='".$key."';");
						lo($dataid."[".$a."][".$ctr."][1]='".$val."';");
						}
					$ctr++;
					}
				}
			if ($mode=="xml") lo("</node>");
			}
		break;
		
		case "group":
		$groupdata=$NATS->GetGroup($_REQUEST['param'][$a]);
		if ($groupdata) // got a valid response
			{
			if ($mode=="js")
				{
				lo($dataid."[".$a."]=new Array();");
				}
			else if ($mode=="xml") lo("<group groupid=\"".$groupdata['groupid']."\" query=\"".$a."\">");
			$ctr=0;
			foreach($groupdata as $key => $val)
				{
				if (!is_numeric($key)) // pesky double-arrays avoided
					{
					if ($mode=="xml") lo(" <".$key.">".$val."</".$key.">");
					else if ($mode=="js")
						{
						lo($dataid."[".$a."][".$ctr."]=new Array;");
						lo($dataid."[".$a."][".$ctr."][0]='".$key."';");
						lo($dataid."[".$a."][".$ctr."][1]='".$val."';");
						}
					$ctr++;
					}
				}
			if ($mode=="xml") lo("</group>");
			}
		break;
		
		
		case "test":
		$testdata=$NATS->GetTest($_REQUEST['param'][$a],true);
		if ($testdata) // got a valid response
			{
			
			if ( (isset($_REQUEST['param1'][$a])) && (isset($_REQUEST['param2'][$a])) )
				{ // get data
				$testdata['period.startx']=0;
				$testdata['period.finishx']=0;
				$testdata['period.tested']=0;
				$testdata['period.passed']=0;
				$testdata['period.warning']=0;
				$testdata['period.failed']=0;
				$testdata['period.untested']=0;
				$testdata['period.average']=0;
				
				if (($testdata['testrecord']==1)||($testdata['testtype']=="ICMP"))
					{
					$sx=smartx($_REQUEST['param1'][$a]);
					$fx=smartx($_REQUEST['param2'][$a]);
					$testdata['period.startx']=$sx;
					$testdata['period.finishx']=$fx;
					
					$q="SELECT alertlevel,COUNT(recordid) AS counter FROM fnrecord WHERE testid=\"".ss($testdata['testid'])."\" AND ";
					$q.="recordx>=".ss($sx)." AND recordx<=".ss($fx)." GROUP BY alertlevel";
					//echo $q;
					$r=$NATS->DB->Query($q);
					while ($row=$NATS->DB->Fetch_Array($r))
						{
						switch ($row['alertlevel'])
							{
							case -1: $testdata['period.untested']+=$row['counter'];
								break;
							case 0: $testdata['period.passed']+=$row['counter'];
								break;
							case 1: $testdata['period.warning']+=$row['counter'];
								break;
							case 2: $testdata['period.failed']+=$row['counter'];
								break;
							}
						$testdata['period.tested']+=$row['counter'];
						}
					$NATS->DB->Free($r);
					
					$q="SELECT AVG(testvalue) FROM fnrecord WHERE testid=\"".ss($testdata['testid'])."\" AND ";
					$q.="recordx>=".ss($sx)." AND recordx<=".ss($fx); //." AND alertlevel IN (0,1)"; // warnings and passes only
					
					$r=$NATS->DB->Query($q);
					
					if ($row=$NATS->DB->Fetch_Array($r))
						{
						$testdata['period.average']=round($row['AVG(testvalue)'],4);
						}
						
					$NATS->DB->Free($r);
					
					}
				}
				
				
			// header
				
			if ($mode=="js")
				{
				lo($dataid."[".$a."]=new Array();");
				lo($dataid."[".$a."][0]=new Array();"); // Keys
				lo($dataid."[".$a."][1]=new Array();"); // Values
				}
			else if ($mode=="xml") lo("<test testid=\"".$testdata['testid']."\" nodeid=\"".$testdata['nodeid']."\" query=\"".$a."\">");
			$ctr=0;
			foreach($testdata as $key => $val)
				{
				if (!is_numeric($key)) // pesky double-arrays avoided
					{
					if ($mode=="xml") lo(" <".$key.">".$val."</".$key.">");
					else if ($mode=="js")
						{
						lo($dataid."[".$a."][0][".$ctr."]='".$key."';");
						lo($dataid."[".$a."][1][".$ctr."]='".$val."';");
						}
					$ctr++;
					}
				}
			if ($mode=="xml") lo("</test>");
			}
		break;
		
		case "alerts":
		$alerts=$NATS->GetAlerts();
		
		$count=count($alerts);
		if ($alerts===false) $count=0; // as showing a 1 in count otherwise
		if ($mode=="xml") lo("<alerts count=\"".$count."\" query=\"".$a."\">");
		else if ($mode=="js") lo($dataid."[".$a."]=new Array();");
		
		if ($alerts) // some were returned
			{
			// nodeid alertlevel	
			for ($alctr=0; $alctr<$count; $alctr++)
				{
				if ($mode=="xml") lo(" <node nodeid=\"".$alerts[$alctr]['nodeid']."\" alertlevel=\"".$alerts[$alctr]['alertlevel']."\">".$alerts[$alctr]['nodeid']."</node>");
				else if ($mode=="js") lo($dataid."[".$a."][".$alctr."]='".$alerts[$alctr]['nodeid']."';");
				}
			}		
			
		if ($mode=="xml") lo("</alerts>");
			
		break;
		
		
		case "testdata":
		// param = testid
		// param1 = startx
		// param2 = finishx
		
		$q="SELECT recordx,testvalue,alertlevel FROM fnrecord WHERE testid=\"".ss($_REQUEST['param'][$a])."\" AND ";
		$sx=smartx($_REQUEST['param1'][$a]);
		$fx=smartx($_REQUEST['param2'][$a]);
		$q.="recordx>=".ss($sx)." AND recordx<=".ss($fx)." ORDER BY recordx ASC";
		
		$r=$NATS->DB->Query($q);
		$count=$NATS->DB->Num_Rows($r);
		
		if ($mode=="xml") lo("<testdata testid=\"".$_REQUEST['param'][$a]."\" counter=\"".$count."\" query=\"".$a."\">");
		else if ($mode=="js")
			{
			lo($dataid."[".$a."]=new Array();");
			}
			
		$ctr=0;
		while ($row=$NATS->DB->Fetch_Array($r))
			{
			if ($mode=="xml")
				{
				lo(" <record recordx=\"".$row['recordx']."\" alertlevel=\"".$row['alertlevel']."\">".$row['testvalue']."</record>");
				}
			else
				{
				lo($dataid."[".$a."][".$ctr."]=new Array();");
				lo($dataid."[".$a."][".$ctr."][0]=".$row['recordx'].";");
				lo($dataid."[".$a."][".$ctr."][1]=".$row['testvalue'].";");
				lo($dataid."[".$a."][".$ctr."][2]=".$row['alertlevel'].";");
				}
			$ctr++;
			}
		$NATS->DB->Free($r);
		
		if ($mode=="xml") lo("</testdata>");
		
		break;
		
		}
	}
	
// Footer and Finish

if ($mode=="xml") lo("</freenats-data>");
else if ($mode=="js")
	{
	if(isset($_REQUEST['callback']))
		{
		lo($_REQUEST['callback']."(".$dataid.");");
		}
	}
	
?>