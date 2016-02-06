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

if ((isset($argc))&&(isset($argv))) // specific node or all nodes
	{
	if ($argc>1)
		{
		$nfilter=$argv[1];
		}
	else $nfilter="";
	}

require("include.php");

$dbt="";

function db($txt,$nl=true) // debug text
{
global $dbt;
echo $txt;
$dbt.=$txt;
if ($nl) 
	{
	echo "\n";
	$dbt.=" <br>\n";
	}
}

$NATS->Start();
if ($nfilter!="") $st=": Node ".$nfilter;
else $st="";
db("NATS Tester Script Starting".$st);

$highalertlevel=-1;
$talertc=0;

// check if already running
$still_running=false;
$cq="SELECT trid,startx FROM fntestrun WHERE fnode=\"".ss($nfilter)."\" AND finishx=0 LIMIT 0,1";
$cr=$NATS->DB->Query($cq);

if ($runrow=$NATS->DB->Fetch_Array($cr))
	{ // yes there is a testrun session for this node(s)
	$timelimit=$NATS->Cfg->Get("test.session.limit",60*60);
	if ( (!is_numeric($timelimit)) || ($timelimit<0) ) $timelimit=60*60; // bogus config value
	
	// n.b. a timelimit of 0 means the session will never expire so...
	if ( ($timelimit>0) && ((time()-$runrow['startx'])>$timelimit) )
		{
		// valid time limit and the difference is more than the limit so close it
		$uq="UPDATE fntestrun SET finishx=1 WHERE trid=".$runrow['trid'];
		$NATS->DB->Query($uq);
		if ($NATS->DB->Affected_Rows()>0) 
			{
			$NATS->Event("Tester Already Running: Cleared",3,"Tester","Stale");
			db("Tester Already Running: Cleared");
			}
		else $NATS->Event("Tester Already Running: Failed to Clear",1,"Tester","Stale"); // weirdness... run anyway
		}
	else $still_running=true; // either never timesout or newer than timelimit ago
	}
$NATS->DB->Free($cr);

// and if it is then don't continue
if ($still_running)
	{
	$NATS->Event("Tester Already Running: Aborted",1,"Tester","Error");
	db("Tester Already Running: Aborted");
	$NATS->Stop();
	exit();
	}


$gq="INSERT INTO fntestrun(startx,fnode) VALUES(".time().",\"".ss($nfilter)."\")";
$NATS->DB->Query($gq);
$trid=$NATS->DB->Insert_Id();
db("Test ID: ".$trid." (Started at ".nicedt(time()).")");
$NATS->Event("Tester ".$trid." Started",5,"Tester","Start");

db(" ");

// Find node to test - must be enabled, have id if set, and be due to be tested (nextrunx)

$q="SELECT * FROM fnnode WHERE nodeenabled=1";
if ($nfilter!="") $q.=" AND nodeid=\"".ss($nfilter)."\"";
$q.=" AND nextrunx<=".time();

$r=$NATS->DB->Query($q);


while ($row=$NATS->DB->Fetch_Array($r))
	{
	$dotests=true;
	$alertlevel=0;
	$alerts=array();
	$alertc=0;
	db("NodeID: ".$row['nodeid']);
	$NATS->Event("Tester ".$trid." Node ".$row['nodeid'],10,"Tester","Node");

	// Scheduling Test In Here - sets dotests to false and alertlevel to -1 untested
	if ($row['scheduleid']!=0) // has a schedule
		{
		db(" Has Schedule: Yes - Checking");
		$run=run_x_in_schedule(time(),$row['scheduleid']);
		if (!$run)
			{
			db(" In Schedule: No - Skipping Tests");
			$NATS->Event("Tester ".$trid." Skipped by Schedule",5,"Tester","Node");
			$dotests=false;
			$alertlevel=-1;
			}
		else db(" In Schedule: Yes");
		}
	
	$eventdata=array( "nodeid" => $row['nodeid'], "in_schedule" => $dotests );
	$NATS->EventHandler("node_test_start",$eventdata);
	
	
	$ptr=0;
	$pal=0;
	

	// Update lastrun and nextrun regardless of dotests
	$q="UPDATE fnnode SET lastrunx=".time().",nextrunx=".next_run_x($row['testinterval'])." WHERE nodeid=\"".ss($row['nodeid'])."\"";
	$NATS->DB->Query($q);

	$pingpassed=false; // this will only be set to true if a test is done and passes - for the "child" nodes
	if ($row['pingtest']&&$dotests)
		{
		db(" Ping Test: Yes");
		$NATS->Event("Tester ".$trid." Pinging Node ".$row['nodeid'],10,"Tester","Ping");
		$ptr=PingTest($row['hostname']);
		$NATS->Event("Tester ".$trid." Ping Node ".$row['nodeid']." Returned ".$ptr,10,"Tester","Ping");
		db(" Ping Returned: ".$ptr);
		if ( ($ptr<=0) && ($NATS->Cfg->Get("test.icmp.attempts","2")>1) )
			{
			$att=$NATS->Cfg->Get("test.icmp.attempts","2");
			for ($a=2; $a<=$att; $a++) // starting on second attempt
				{
				// try again...
				test_sleep();
				db(" Trying Ping Again - X".$a);
				$NATS->Event("Tester ".$trid." Ping X".$a." Node ".$row['nodeid'],10,"Tester","Ping");
				$ptr=PingTest($row['hostname']);
				$NATS->Event("Tester ".$trid." Ping Node ".$row['nodeid']." Returned ".$ptr,10,"Tester","Ping");
				db(" Ping Returned: ".$ptr);
				if ($ptr>0) $a=$att+1; // break out of the loop
				}
			}
			
		if ($ptr<=0) 
			{
			$alertlevel=2;
			db(" Ping Test: Failed");
			$alerts[$alertc++]="ping failed";
			$pal=2;
			}
		else 
			{
			db(" Ping Test: Passed");
			$pingpassed=true;
			}
		
		// pingtest output bodge
		// is there a test entry for ICMP
		$fq="SELECT localtestid FROM fnlocaltest WHERE nodeid=\"".$row['nodeid']."\" AND testtype=\"ICMP\"";
		$fr=$NATS->DB->Query($fq);
		$ltid_icmp="";
		if ($irow=$NATS->DB->Fetch_Array($fr))
			{ // exists
			$uq="UPDATE fnlocaltest SET alertlevel=".$pal.",lastrunx=".time().",lastvalue=".$ptr.",testrecord=1,testinterval=0 WHERE localtestid=".$irow['localtestid'];
			$ltid_icmp=$irow['localtestid'];
			//echo $uq;
			$NATS->DB->Query($uq);
			}
		else
			{ // doesn't exist
			$uq="INSERT INTO fnlocaltest(nodeid,testrecord,testinterval,testtype,alertlevel,lastrunx,lastvalue) VALUES(\"".$row['nodeid']."\",1,0,\"ICMP\",".$pal.",".time().",".$ptr.")";
			//echo $uq;
			$NATS->DB->Query($uq);
			$ltid_icmp=$NATS->DB->Insert_Id();
			}
		$NATS->DB->Free($fr);
		
		// record the ICMP bodge-test here
		$rq="INSERT INTO fnrecord(testid,recordx,testvalue,alertlevel,nodeid) VALUES(\"L".$ltid_icmp."\",".time().",".$ptr.",".$pal.",\"".$row['nodeid']."\")";
		$NATS->DB->Query($rq);
		//echo $rq." ".$NATS->DB->Affected_Rows()."\n";
		
		}
	else
		{ // further ICMP bodge - update to -1 or do nothing if the test doesn't exist
		$uq="UPDATE fnlocaltest SET alertlevel=-1,lastrunx=".time()." WHERE nodeid=\"".$row['nodeid']."\" AND testtype=\"ICMP\"";
		$NATS->DB->Query($uq);
		}

	if ($dotests&&($row['pingfatal'])&&($ptr<=0))
		{
		db(" Ping Fatal: Yes - Not Continuing");
		$NATS->Event("Tester ".$trid." Ping Fatal for Node ".$row['nodeid'],10,"Tester","Ping");
		$dotests=false;
		}

	 	// do the tests - only actually exec if dotests true

	 	$first_test=true;
	 	
		db("Doing Local Tests");
		$NATS->Event("Tester ".$trid." Testing Node ".$row['nodeid'],10,"Tester","Test");
		$q="SELECT * FROM fnlocaltest WHERE nodeid=\"".$row['nodeid']."\" AND testtype!=\"ICMP\" AND testenabled=1 ORDER BY localtestid ASC";
		$res=$NATS->DB->Query($q);
		while ($lrow=$NATS->DB->Fetch_Array($res))
			{
			if ($lrow['nextrunx']<=time()) $testdue=true;
			else $testdue=false;
				
			if ($first_test)
				{
				$first_test=false;
				if ($row['pingtest']==1) test_sleep(); // sleep if has done a ping
				}
			else test_sleep();
			
			if ($testdue)
				{
			
				$eventdata=array("nodeid"=>$row['nodeid'],"testid"=>"L".$lrow['testparam'],"testtype"=>$lrow['testtype']);
				$NATS->EventHandler("localtest_start",$eventdata);
					
				db(" Test: ".$lrow['testtype']." (".$lrow['testparam'].")");
				
				// Build parameter array
				$params=array();
				$params[0]=$lrow['testparam']; // pass standard param in as 0
				for ($a=1; $a<10; $a++)
					{
					$parstr="testparam".$a;
					$params[$a]=$lrow[$parstr];
					}
				
				if ($dotests)
					{
					$NATS->Event("Tester ".$trid." Node ".$row['nodeid']." Doing ".$lrow['testtype']."(".$lrow['testparam'].")",10,"Tester","Test");
					$result=DoTest($lrow['testtype'],$lrow['testparam'],$row['hostname'],$lrow['timeout'],$params,$row['nodeid']);
					$NATS->Event("Tester ".$trid." Node ".$row['nodeid']." Result ".$result." from ".$lrow['testtype']."(".$lrow['testparam'].")",10,"Tester","Test");
					db(" Result: ".$result);
					}
				else $result=0;
				
				if ($dotests)
				{
				// evaluation
				if ($lrow['simpleeval']==1) $lvl=SimpleEval($lrow['testtype'],$result);
				else $lvl=nats_eval("L".$lrow['localtestid'],$result);
				db(" Eval: ".$lvl);
				
				// put in the custom retries based on attempts here - we KNOW dotests is on so don't need to worry about untested status
				$att=$lrow['attempts'];
				if ( ($lvl!=0) && (is_numeric($att)) && ($att>1) )
					{
					for ($a=2; $a<=$att; $a++)
						{
						test_sleep();
						db(" Test: ".$lrow['testtype']." (".$lrow['testparam'].") X".$a);
						$NATS->Event("Tester ".$trid." Node ".$row['nodeid']." X".$a." Doing ".$lrow['testtype']."(".$lrow['testparam'].")",10,"Tester","Test");
						$result=DoTest($lrow['testtype'],$lrow['testparam'],$row['hostname'],$lrow['timeout'],$params,$row['nodeid']);
						db(" Result: ".$result);
						if ($lrow['simpleeval']==1) $lvl=SimpleEval($lrow['testtype'],$result);
						else $lvl=nats_eval("L".$lrow['localtestid'],$result);
						db(" Eval: ".$lvl);
						if ($lvl==0) $a=$att+1; // test passed
						}
					}
				
				// $lvl is now the last lvl regardless of where it came from
						
				if ($lvl>$alertlevel) $alertlevel=$lvl;
				if ($lvl>0)
					{
					if ($lrow['testname']=="") $s=$lrow['testtype']."/".substr($lrow['testparam'],0,5)." ";
					else $s=$lrow['testname']." ";
					/*
					if ($lvl>1) $s.=$NATS->Cfg->Get("site.text.failed","failed");
					else $s.=$NATS->Cfg->Get("site.text.warning","warning");
					*/
					$s.=oText($lvl);
					// site.alert.showvalue -- includes value in alert messages if numeric
					// site.alert.showtext -- includes value in alert messages if textual
					if (is_numeric($result))
						{
						if ($NATS->Cfg->Get("site.alert.showvalue",0)==1) $s.=" (".$result.")";
						}
					else // non-numeric
						{
						if ($NATS->Cfg->Get("site.alert.showtext",0)==1) $s.=" (".$result.")";
						}
					$alerts[$alertc++]=$s;
					}
				} else $lvl=-1;
					
				// record it
				if ($lrow['testrecord']==1)
					{
					$tid="L".$lrow['localtestid'];
					$iq="INSERT INTO fnrecord(testid,nodeid,alertlevel,testvalue,recordx) VALUES(";
					$iq.="\"".$tid."\",\"".$row['nodeid']."\",".$lvl.",".$result.",".time().")";
					$NATS->DB->Query($iq);
					db(" Recording Test");
					}
				if ((!isset($result))||(!is_numeric($result))) $result=0; // safety net
					
				// update localtest record
				$uq="UPDATE fnlocaltest SET lastrunx=".time().",nextrunx=".next_run_x($lrow['testinterval']).",alertlevel=".$lvl.",lastvalue=".$result." WHERE localtestid=".$lrow['localtestid'];
				$NATS->DB->Query($uq);
				
				$eventdata=array("nodeid"=>$row['nodeid'],"testid"=>"L".$lrow['testparam'],"testtype"=>$lrow['testtype'],"alertlevel"=>$lvl);
				$NATS->EventHandler("localtest_finish",$eventdata);
				}
				
			else // test not due so take pre-existing level for it
				{
				$lvl=$lrow['alertlevel'];
				if (($lvl>0)&&($lvl>$alertlevel)) $alertlevel=$lvl;
				}
			
			
			}
			
	// Node-side testy magic
	db("Nodeside Testing");
	$freshdata=false;
	if ( $dotests && ($row['nsenabled']==1) && ($row['nspullenabled']==1) ) // should be doing a pull
		{
		$pullalert=$row['nspullalert']; // what happened the last time we tried
		
		if ($row['nsnextx']<=time()) // the time is right
			{
			db(" Pulling Data");
			$pull_result=$NATS->Nodeside_Pull($row['nodeid']);
			
			if ($pull_result===false) // Pull Failed
				{
				db(" Pull Failed");
				$pullalert=1; // alert
				$alerts[$alertc++]="pull failed";
				$alertlevel=2;
				}
			else // Pull Worked
				{
				$freshdata=true;
				$pullalert=0; // ok
				db(" Pull Succeeded");
				}
				
				
			db(" Updating Pull nslast/nextx and nspullalert");
			$uq="UPDATE fnnode SET nsnextx=".next_run_x($row['nsinterval']).",nspullalert=".$pullalert.",nslastx=".time()." WHERE nodeid=\"".$row['nodeid']."\"";
			$NATS->DB->Query($uq);
			if ($NATS->DB->Affected_Rows()<=0) db(" - Failed");
			}
		/*
		// Process for alerts in here - whether pulled or not!
		$tq="SELECT testtype,testname,alertlevel FROM fnnstest WHERE nodeid=\"".$row['nodeid']."\" AND testenabled=1 AND testalerts=1 AND alertlevel>0";
		$tr=$NATS->DB->Query($tq);
		while ($trow=$NATS->DB->Fetch_Array($tr))
			{
			if ($trow['testname']=="") $tname=$trow['testtype'];
			else $tname=$trow['testname'];
			if ($freshdata) $alerts[$alertc++]=$tname." ".oText($trow['alertlevel']); // only record text to log if fresh
			if ($trow['alertlevel']>$alertlevel) $alertlevel=$trow['alertlevel'];
			}
		*/
			
		// and finally again use pullalert - this is either the new value if a pull was attempted or just remains the same as the old one
		// if pull not scheduled yet
		if ($pullalert>0) $alertlevel=2; // so mark a failure

		}
		
	if ( ($dotests && ($row['nsenabled']==1) && ($row['nspullenabled']==1)) ||		// pull and tests are on
		(($row['nsenabled']==1)&&($row['nspushenabled']==1)) )	// or pushed
		{
		if ($row['nsfreshpush']==1)
			{
			$freshdata=true;
			$uq="UPDATE fnnode SET nsfreshpush=0 WHERE nodeid=\"".$row['nodeid']."\"";
			$NATS->DB->Query($uq);
			}
		// Process for alerts in here - whether pulled or not!
		$tq="SELECT testtype,testname,alertlevel,lastvalue FROM fnnstest WHERE nodeid=\"".$row['nodeid']."\" AND testenabled=1 AND testalerts=1 AND alertlevel>0";
		$tr=$NATS->DB->Query($tq);
		while ($trow=$NATS->DB->Fetch_Array($tr))
			{
			if ($trow['testname']=="") $tname=$trow['testtype'];
			else $tname=$trow['testname'];
			if ($freshdata) // only record text to log if fresh
				{
				$s=$tname." ".oText($trow['alertlevel']); 
				$result=$trow['lastvalue'];
				if (is_numeric($result))
						{
						if ($NATS->Cfg->Get("site.alert.showvalue",0)==1) $s.=" (".$result.")";
						}
					else // non-numeric
						{
						if ($NATS->Cfg->Get("site.alert.showtext",0)==1) $s.=" (".$result.")";
						}
				$alerts[$alertc++]=$s;
				}
			if ($trow['alertlevel']>$alertlevel) $alertlevel=$trow['alertlevel'];
			}
		}
			
	$NATS->Event("Tester ".$trid." Finished Node ".$row['nodeid'],10,"Tester","Node");

	$eventdata=array( "nodeid" => $row['nodeid'], "alertlevel" => $alertlevel );
	$NATS->EventHandler("node_test_finish",$eventdata);

	db("Highest Alert Level: ".$alertlevel);
	db("Alert Count        : ".$alertc);
	$als="";
	foreach($alerts as $al) $als.=$al.", ";
	db("Alerts: ".$als);

	$NATS->SetAlerts($row['nodeid'],$alertlevel,$alerts);
	
	// This is where child/slave nodes spawn
	
	// $pingpassed bool holds if pingtest has passed
	// $alertlevel holds the overall status
	$chq="SELECT nodeid,masterjustping FROM fnnode WHERE masterid=\"".$row['nodeid']."\"";
	//echo $chq;
	
	$chr=$NATS->DB->Query($chq);
	$spawnlist=array();
	
	while ($child=$NATS->DB->Fetch_Array($chr))
		{
		if (($child['masterjustping']==1)&&($pingpassed)) $spawnlist[]=$child['nodeid'];
		else if ($alertlevel==0) $spawnlist[]=$child['nodeid'];
		// logic: if the child requires a ping and ping has passed ok then spawn it
		// otherwise (pass on any alert) if everything has passed spawn it
		}
	$NATS->DB->Free($chr);
	
	if (count($spawnlist)>0)
		{
		$cmd="php ./test-threaded.php";
		foreach($spawnlist as $child)
			$cmd.=" ".$child;
		$cmd.=" > /tmp/ns.master.".$row['nodeid']." &";
		db("Children Spawning: ".$cmd);
		exec($cmd);
		}
	
	
	// End of the node... carry forward highest level

	db(" ");
	
	if ($alertlevel>$highalertlevel) $highalertlevel=$alertlevel;
	$talertc+=$alertc;
	
	}



db("Finished Tests... Finishing Off");
db("Summary: Tester ".$trid." Highest Level ".$highalertlevel.", Alerts ".$talertc);
if ($highalertlevel>-1)
	{
	$uq="UPDATE fntestrun SET finishx=".time().",routput=\"".ss($dbt)."\" WHERE trid=".$trid;
	$NATS->DB->Query($uq);
	}
else
	{
	$uq="DELETE FROM fntestrun WHERE trid=".$trid;
	$NATS->DB->Query($uq);
	}


$NATS->Event("Tester ".$trid." Highest Level ".$highalertlevel.", Alerts ".$talertc,7,"Tester","Stat");
$NATS->Event("Tester ".$trid." Finished",5,"Tester","Stop");

// in here for now...
$NATS->ActionFlush();

$NATS->Stop();
db("NATS Stopped... Finished");
?>

