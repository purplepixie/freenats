<?php // freenats.inc.php
/* -------------------------------------------------------------
This file is part of FreeNATS

FreeNATS is (C) Copyright 2008-2017 PurplePixie Systems

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

class TFreeNATS
{
var $init=false;
var $DB;
var $Cfg;
var $Tests;
var $Lang;
var $RSS;
var $Version="1.17.0";
var $Release="a";
var $EventHandlers=array();

var $PageErrors=array();

var $userdata = array();
var $usergrouplock = array();

function TFreeNATS()
	{
	$this->Tests=new TNATS_Tests(); // need this available during the include phase pre-start
	}

function Start()
	{
	if ($this->init) return 0;
	global $BaseDir,$fnSkipSiteIncludes;
	if ( (!isset($BaseDir)) || ($BaseDir=="") ) $BaseDir="./";
	$this->DB=new TNATS_DB();
	$this->Cfg=new TNATS_Cfg();
	$this->DB->Connect();
	$this->Cfg->Load($this->DB);
	// Site Includes
	// Tests
	if ( (!isset($fnSkipSiteIncludes)) || ($fnSkipSiteIncludes === false) )
		{
	
		if ($this->Cfg->Get("site.include.tests",0)==1)
			{
			foreach(glob($BaseDir."site/tests/*.php") as $phpfile)
				{
				$this->Event("Including ".$phpfile,10,"NATS","Start");
				include_once($phpfile);
				}
			}
		// Events
		if ($this->Cfg->Get("site.include.events",0)==1)
			{
			foreach(glob($BaseDir."site/events/*.php") as $phpfile)
				{
				$this->Event("Including ".$phpfile,10,"NATS","Start");
				include_once($phpfile);
				}
			}
		}

	// Timezones
	$zone=$this->Cfg->Get("site.timezone","");
	if ($zone != "")
		date_default_timezone_set($zone);
		
	// RSS
	$this->RSS = new NATS_RSS();
	
	// Language
	$this->Lang=new TNATS_Lang();
	if (isset($_COOKIE['fn_lang']) && ($_COOKIE['fn_lang']!="") ) $l=$_COOKIE['fn_lang'];
	else $l=$this->Cfg->Get("site.language","");
	$this->Lang->Load($l);
	
		
	$this->init=true;
	}
	
function Stop()
	{
	$t=$this->Cfg->Get("freenats.tracker","");
	if ( ($t>0) && ($t!="") )
		{
		$day=date("Ymd");
		if ($this->Cfg->Get("freenats.tracker.last","")!=$day)
			{
			$this->Cfg->Set("freenats.tracker.last",$day);
			$this->PhoneHome();
			}
		}
		
	$this->DB->Disconnect();
	$this->init=false;
	}	
	
function Event($logevent,$loglevel=1,$modid="NONE",$catid="NONE")
	{
	global $NATS_Session;
	if ((isset($NATS_Session))&&($NATS_Session->auth)) $username=$NATS_Session->username;
	else $username="";
	$l=$this->Cfg->Get("log.level");
	//echo "** $l **\n";
	if ( $l=="" ) $l=10; // debug logging if no variable
	if ( $l < $loglevel ) return false;
	if (strlen($logevent)>249) $logevent=substr($logevent,0,245)."...";
	$q="INSERT INTO fnlog(postedx,modid,catid,loglevel,logevent,username) VALUES(".time().",";
	$q.="\"".ss($modid)."\",\"".ss($catid)."\",".ss($loglevel).",\"".ss($logevent)."\",\"".ss($username)."\")";
	//echo $q;
	$this->DB->Query($q,false);
	}

function AlertAction($nodeid,$alertlevel,$change,$alerttext)
	{
	//echo "Called for node: ".$nodeid."\n";
	if ($change==0) return false; // no change
	if (trim($alerttext)=="") return false; // no text so a bogus event i.e. -1 to 0
	//echo $nodeid.":".$alertlevel.":".$change.":".$alerttext."\n";
	// get all the alertactions for this node id
	$q="SELECT aaid FROM fnnalink WHERE nodeid=\"".ss($nodeid)."\"";
	$r=$this->DB->Query($q);
	while ($arow=$this->DB->Fetch_Array($r))
		{
		// get details for this alert action
		$aq="SELECT * FROM fnalertaction WHERE aaid=".$arow['aaid']." LIMIT 0,1";
		
		$ar=$this->DB->Query($aq);
		$aa=$this->DB->Fetch_Array($ar);
		$this->DB->Free($ar);
		
		// UGGGGGGGG continue!!
		// if the type is blank or disabled skip
		if ( ($aa['atype']=="") || ($aa['atype']=="Disabled") ) continue;
		// if warnings aren't set and it is a warning skip
		if ( ($aa['awarnings']==0) && ($alertlevel==1) ) continue;
		// if decreases aren't set and it is a decrease skip
		if ( ($aa['adecrease']==0) && ($change<1) ) continue;
		// if has a schedule and it dictates not to run now then skip
		if (($aa['scheduleid']!=0)&&(!run_x_in_schedule(time(),$aa['scheduleid']))) continue;
		
		// made it this far
		
		$ndata=$nodeid.": ".$alerttext;
		$this->ActionAddData($arow['aaid'],$ndata);
		
		/* // spun to ActionAddData
		if ($aa['mdata']!="") $ndata=$aa['mdata']."\n".$nodeid.": ".$alerttext;
		else $ndata=$nodeid.": ".$alerttext;
		$uq="UPDATE fnalertaction SET mdata=\"".ss($ndata)."\" WHERE aaid=".$arow['aaid'];
		//echo $uq."\n";
		$this->DB->Query($uq);
		*/
		}
	}
	
function ActionAddData($aaid, $newmdata)
	{
	$q="SELECT aaid,mdata FROM fnalertaction WHERE aaid=".ss($aaid)." LIMIT 0,1";
	$r=$this->DB->Query($q);
	if (!$row=$this->DB->Fetch_Array($r)) return false;
	$this->DB->Free($r);
	
	if ($row['mdata']!="") $ndata=$row['mdata']."\n".$newmdata;
	else $ndata=$newmdata;
	
	$uq="UPDATE fnalertaction SET mdata=\"".ss($ndata)."\" WHERE aaid=".$row['aaid'];
	$this->DB->Query($uq);
	return true;
	}
	
function ActionFlush()
	{
	global $allowed,$BaseDir; // allowed chars from screen in YA BODGE
	$q="SELECT * FROM fnalertaction WHERE mdata!=\"\"";
	$r=$this->DB->Query($q);
	while ($row=$this->DB->Fetch_Array($r))
		{
			
			$doalert=true;
			
		// clear mdata right at the start to get around duplicate emails whilst processing
			$q="UPDATE fnalertaction SET mdata=\"\" WHERE aaid=".$row['aaid'];
			$this->DB->Query($q);
			
			if ($this->DB->Affected_Rows()<=0) // already flushed or failed to flush
				{
				$doalert=false;
				$this->Event("Alert Action Already Flushed - Skipping",8,"Flush","Action");
				}
			
		// alert counter
		$td=date("Ymd");
		if ($td!=$row['ctrdate']) // new day or no flush record
			{
			$q="UPDATE fnalertaction SET ctrdate=\"".$td."\",ctrtoday=1 WHERE aaid=".$row['aaid'];
			$this->DB->Query($q);
			}
		else
			{
			
				if ( ($row['ctrlimit']==0) || ($row['ctrlimit']>$row['ctrtoday']) ) // no limit or below
					{
					$q="UPDATE fnalertaction SET ctrtoday=ctrtoday+1 WHERE aaid=".$row['aaid'];
					$this->DB->Query($q);
					}
				else // at or over limit
					{
					$this->Event("Alert Action Limit Reached - Skipping",2,"Flush","Action");
					$doalert=false;
					}
			
			}
			
			
		if ($row['atype']=="email")
			{
			if ($row['esubject']==0) $sub="";
			else if ($row['esubject']==1) $sub=$this->Cfg->Get("alert.subject.short","FreeNATS Alert");
			else $sub=$this->Cfg->Get("alert.subject.long","** FreeNATS Alert **");
			$body="";
			if ($row['etype']==0) $body=$row['mdata'];
			else 
				{
				$body=$this->Cfg->Get("alert.body.header","FreeNATS Alert,");
				$body.="\r\n\r\n".$row['mdata']."\r\n\r\n";
				$body.=$this->Cfg->Get("alert.body.footer","");
				$body.="\r\n-- FreeNATS @ ".nicedt(time());
				}
			//$tolist=preg_split("[\n\r]",$row['etolist']);
			$tolist=array();
			$f=0;
			$tolist[0]="";
			for ($a=0; $a<strlen($row['etolist']); $a++)
				{
				$chr=$row['etolist'][$a];
				//echo $chr;
				if (strpos($allowed,$chr)===false) // special char
					{
					$f++;
					$tolist[$f]="";
					}
				else
					{
					$tolist[$f].=$chr;
					}
				}
			
			foreach($tolist as $toaddr)
				{
				$toaddr=nices($toaddr);
				if ($toaddr!="")
					{
					$smtpserver=$this->Cfg->Get("mail.smtpserver","");
					if ($smtpserver=="")
						{
						// mail() method - local delivery
						$header="From: ".$row['efrom']."\r\n";
						if ($doalert)
							{
							mail($toaddr,$sub,$body,$header);
							$this->Event("Sent alert email to ".$toaddr,4,"Flush","Email");
							}		
						}
					else // use phpmailer direct SMTP delivery
						{
						include_once($BaseDir."phpmailer/class.phpmailer.php");
						$fromname=$this->Cfg->Get("mail.fromname","");
						if ($fromname=="") $fromname="FreeNATS";
						$smtpusername=$this->Cfg->Get("mail.smtpusername",""); // removed .
						if ($smtpusername!="") $smtpauth=true;
						else $smtpauth=false;
						$smtppassword=$this->Cfg->Get("mail.smtppassword",""); // removed .
						$smtphostname=$this->Cfg->Get("mail.smtphostname",""); // removed .
						$smtpsec=$this->Cfg->Get("mail.smtpsecure","");
						$mail=new PHPMailer();
						$mail->IsSMTP();
						$mail->Host=$smtpserver;
						$mail->From=$row['efrom'];
						$mail->FromName=$fromname;
						$mail->AddAddress($toaddr);
						$mail->Subject=$sub;
						$mail->Body=$body;
						$mail->WordWrap=50;
						if ($smtphostname!="") $mail->Hostname=$smtphostname;
						if ($smtpauth)
							{
							$mail->SMTPAuth=true;
							$mail->Username=$smtpusername;
							$mail->Password=$smtppassword;
							}
						if ($smtpsec!="") $mail->SMTPSecure=$smtpsec;
						if (!$mail->Send())
							{ // failed
							$this->Event("phpMailer to ".$toaddr." failed",1,"Flush","Email");
							$this->Event("phpMailer Error: ".$mail->ErrorInfo,1,"Flush","Email");
							}
						else
							{
							$this->Event("phpMailer Sent Email To ".$toaddr,4,"Flush","Email");
							}
						}
						
					}
				}
				
				
				
			}
		else if ($row['atype']=="url")
			{
			// url send
			if ($row['etype']==0) $body=$row['mdata'];
			else $body="FreeNATS Alert,\r\n".$row['mdata']."\r\n--FreeNATS @ ".nicedt(time());
			
			$body=urlencode($body);
			$tolist=array();
			$f=0;
			$tolist[0]="";
			for ($a=0; $a<strlen($row['etolist']); $a++)
				{
				$chr=$row['etolist'][$a];
				//echo $chr;
				if (strpos($allowed,$chr)===false) // special char
					{
					$f++;
					$tolist[$f]="";
					}
				else
					{
					$tolist[$f].=$chr;
					}
				}
			
			foreach($tolist as $tourl)
				{
				if ($doalert)
					{
					$url=$tourl.$body;
					$fp=@fopen($url,"r");
					if ($fp>0) fclose($fp);
					else $this->Event("URL Alert Failed ".$url,1,"Flush","URL");
					$this->Event("URL Alert ".$url,4,"Flush","URL");
					}
				}
			
			
			}
		else if ($row['atype']=="mqueue")
			{
			// message queue
			$eventdata=array("aaid"=>$row['aaid'],"name"=>$row['aname'],"data"=>$row['mdata']);
			$result=$this->EventHandler("alert_action",$eventdata);
			if ($result===false) // put the data back into the queue
				{
				$q="UPDATE fnalertaction SET mdata=\"".$row['mdata']."\"+mdata WHERE aaid=".$row['aaid'];
				$this->DB->Query($q);
				if ($this->DB->Affected_Rows()<=0)
					$this->Event("Persist MDATA Failed for AAID ".$row['aaid'],2,"Flush","MQueue");
				}
			else $this->Event("Queue Cleared for AAID ".$row['aaid']." by Handler",4,"Flush","MQueue");
			}
			
		}
	}	

function GetAlerts()
	{
	global $NATS_Session;
	$q="SELECT nodeid,alertlevel FROM fnalert WHERE closedx=0";
	$r=$this->DB->Query($q);
	$c=0;
	$al=array();
	while ($row=$this->DB->Fetch_Array($r))
	{
		if ($this->isUserAllowedNode($NATS_Session->username,$row['node']))
		{
			$al[$c]['nodeid']=$row['nodeid'];
			$al[$c]['alertlevel']=$row['alertlevel'];
			$c++;
		}
	}
	if ($c>0) return $al;
	else return false;
	}
	
function SetAlerts($nodeid,$alertlevel,$alerts="")
	{
	if ($alerts=="") $alerts=array();
	// get current alert level
	$q="SELECT alertlevel,nodealert FROM fnnode WHERE nodeid=\"".ss($nodeid)."\"";
	$r=$this->DB->Query($q);
	$row=$this->DB->Fetch_Array($r);
	$this->DB->Free($r);
	$cal=$row['alertlevel'];
	
	$eventdata=array("nodeid"=>$nodeid,"alertlevel"=>$alertlevel,
		"oldalertlevel"=>$cal);
	$this->EventHandler("set_alerts",$eventdata);
		
	if ($alertlevel!=$cal)
		{
		// update table
		$q="UPDATE fnnode SET alertlevel=".ss($alertlevel)." WHERE nodeid=\"".ss($nodeid)."\"";
		$this->DB->Query($q);
		}
		
	// do not continue if node alert isn't set
	if ($row['nodealert']!=1) return 0;
	// or if untested
	if ($alertlevel<0) return 0;
		
	// ALERTS
	// is there an existing alert for this node
	$q="SELECT alertid,alertlevel FROM fnalert WHERE nodeid=\"".ss($nodeid)."\" AND closedx=0";
	$r=$this->DB->Query($q);
	if ($row=$this->DB->Fetch_Array($r))
		{ // yes there is
		// if new alert level is 0 let's close it
		if ($alertlevel==0)
			{
			$alertid=$row['alertid'];
			$q="UPDATE fnalert SET closedx=".time()." WHERE alertid=".$row['alertid'];
			$this->DB->Query($q);
			$closetext=$this->Cfg->Get("site.text.closed","Alert Closed");
			if (is_array($alerts)) $alerts[]=$closetext;
			else
				{
				$alerts=array($alerts); // add as first element to new array
				$alerts[]=$closetext;
				}
			$eventdata=array("nodeid"=>$nodeid);
			$this->EventHandler("alert_close",$eventdata);
			}
		else
			{
			$alertid=$row['alertid'];
			// otherwise update the alert to the new value (was: regardless, now just if not a 0)
			$q="UPDATE fnalert SET alertlevel=".ss($alertlevel)." WHERE alertid=".$alertid;
			$this->DB->Query($q);
			}
		}
	else
		{ // no there's not
		$cal=0; // the cal (current alert level) goes to zero if it's a new alert so alert_actions fire ok
		if ($alertlevel>0) // only if an actual alert
			{
			$q="INSERT INTO fnalert(nodeid,alertlevel,openedx) VALUES(";
			$q.="\"".ss($nodeid)."\",".ss($alertlevel).",".time().")";
			$this->DB->Query($q);
			$alertid=$this->DB->Insert_Id();
			$eventdata=array("nodeid"=>$nodeid);
			$this->EventHandler("alert_open",$eventdata);
			}
		}
	// ALERT LOG with $alertid
	$t=time();
	$at="";
	if (is_array($alerts))
		{
		foreach($alerts as $alert)
			{
			if (isset($alertid)) // misses on manual runs methinx
				{
				if ($at!="") $at.=", ";
				$at.=$alert;
				//echo $at."\n";
				$iq="INSERT INTO fnalertlog(alertid,postedx,logentry) VALUES(";
				$iq.=$alertid.",".$t.",\"".ss($alert)."\")";
				//echo $iq;
				$this->DB->Query($iq);
				}
			}
		}
		
	$this->AlertAction($nodeid,$alertlevel,$alertlevel-$cal,$at);
	
		
		
	}

function NodeAlertLevel($nodeid)
	{
	$q="SELECT alertlevel FROM fnnode WHERE nodeid=\"".ss($nodeid)."\"";
	$r=$this->DB->Query($q);
	if ($row=$this->DB->Fetch_Array($r)) return $row['alertlevel'];
	else return -1;
	}	

function GroupAlertLevel($groupid)
	{
	$lvl=-1;
	$q="SELECT nodeid FROM fngrouplink WHERE groupid=\"".ss($groupid)."\"";
	$r=$this->DB->Query($q);
	while ($row=$this->DB->Fetch_Array($r))
		{
		$nl=$this->NodeAlertLevel($row['nodeid']);
		if ($nl>$lvl) $lvl=$nl;
		}
	$this->DB->Free($r);
	return $lvl;
	}
	
function PhoneHome($mode=0,$type="ping") // 0 - php, 1 - html, 2 - data
{
if ($mode<2)
	{
	$qs="?type=".$type."&data=v=".$this->Version;
	if (isset($_SERVER['REMOTE_ADDR']))
		$qs.=",ip=".$_SERVER['REMOTE_ADDR'];
	$ploc="http://www.purplepixie.org/freenats/report/";
	if ($mode==1) $ploc.="ping.html";
	else $ploc.="ping.php";
	
	$ploc.=$qs;
	
	$lp=@fopen($ploc,"r");
	if ($lp>0) @fclose($lp);
	}
else
	{
	// data post -- !!
	}
}

function GetNode($nodeid)
	{
	$return_row=false;
	$q="SELECT * FROM fnnode WHERE nodeid=\"".ss($nodeid)."\" LIMIT 0,1";
	$r=$this->DB->Query($q);
	if ($row=$this->DB->Fetch_Array($r))
		$return_row=true;
		
	$this->DB->Free($r);
	if ($return_row) // found a valid
		{
		if ($row['nodename']!="") $row['name']=$row['nodename']; // make a "nice" name for it
		else $row['name']=$row['nodeid'];
		
		$row['alerttext']=oText($row['alertlevel']); // textual alert status
		
		$row['lastrundt']=nicedt($row['lastrunx']); // text date-time last run
		$row['lastrunago']=dtago($row['lastrunx'],false); // last run ago
		
		// protection
		$row['nskey']="";
		
		return $row;
		}
	else
		return false; // or failed
	}
	
function GetNodes()
{
	$out=array();
	$q="SELECT * FROM fnnode";
	$r=$this->DB->Query($q);
	
	while ($row=$this->DB->Fetch_Array($r))
	{
		if ($row['nodename']!="") $row['name']=$row['nodename']; // make a "nice" name for it
		else $row['name']=$row['nodeid'];
		
		$row['alerttext']=oText($row['alertlevel']); // textual alert status
		
		$row['lastrundt']=nicedt($row['lastrunx']); // text date-time last run
		$row['lastrunago']=dtago($row['lastrunx'],false); // last run ago
		
		// protection
		$row['nskey']="";
		
		$out[$row['nodeid']]=$row;
	}
	$this->DB->Free($r);
	
	return $out;
}

	
function GetNodeTests($nodeid)
	{ // returns an array of testids for the node (enabled tests only)
	$tests=array();
	
	// local tests
	$q="SELECT localtestid FROM fnlocaltest WHERE testenabled=1 AND nodeid=\"".ss($nodeid)."\" ORDER BY localtestid ASC";
	$r=$this->DB->Query($q);
	while ($row=$this->DB->Fetch_Array($r))
		{
		$tests[]="L".$row['localtestid'];
		}
	$this->DB->Free($r);
	
	// nodeside
	$q="SELECT nstestid FROM fnnstest WHERE testenabled=1 AND nodeid=\"".ss($nodeid)."\" ORDER BY testtype ASC";
	$r=$this->DB->Query($q);
	while ($row=$this->DB->Fetch_Array($r))
		{
		$tests[]="N".$row['nstestid'];
		}
	$this->DB->Free($r);
	
	return $tests;
	}
	
function SetNode($nodeid,$data)
	{
	$q="UPDATE fnnode SET ";
	$first=true;
	foreach($data as $key => $val)
		{
		if ($first) $first=false;
		else $q.=",";
		$q.=ss($key)."=\"".ss($val)."\"";
		}
	$q.=" WHERE nodeid=\"".ss($nodeid)."\"";
	$this->DB->Query($q);
	if ($this->DB->Affected_Rows()>0) return true;
	
	if ($this->DB->Error()) // query failed
		{
		$errstr1="Query Failed: ".$q;
		$errstr2="Query Failed: ".$this->DB->Error_String();
		$this->Event($errstr1,2,"Node","Set");
		$this->Event($errstr1,2,"Node","Set");
		return false;
		}
	return true; // query succeeded but nothing was updated
	}
	
function EnableNode($nodeid,$enabled=true)
	{
	if ($enabled) $ne=1;
	else $ne=0;
	$data=array("nodeenabled"=>$ne);
	return $this->SetNode($nodeid,$data);
	}
	
function DisableNode($nodeid)
	{
	return $this->EnableNode($nodeid,false);
	}
	
function SetNodeSchedule($nodeid,$scheduleid)
	{
	$data=array("scheduleid"=>$scheduleid);
	return $this->SetNode($nodeid,$data);
	}
	
function GetGroup($groupid)
	{
	$q="SELECT * FROM fngroup WHERE groupid=".ss($groupid)." LIMIT 0,1";
	$r=$this->DB->Query($q);
	if (!$row=$this->DB->Fetch_Array($r)) return false;
	
	$this->DB->Free($r);
	$row['alertlevel']=$this->GroupAlertLevel($groupid);
	$row['alerttext']=oText($row['alertlevel']);
	return $row;
	}
	
function GetGroups()
{
	$out=array();
	$q="SELECT * FROM fngroup";
	$r=$this->DB->Query($q);
	
	while ($row=$this->DB->Fetch_Array($r))
	{
		$row['alertlevel']=$this->GroupAlertLevel($row['groupid']);
		$row['alerttext']=oText($row['alertlevel']);
		$out[$row['groupid']]=$row;
	}
	
	$this->DB->Free($r);
	return $out;
}
	
function GetTest($testid,$protect=false)
	{
	if ($testid=="") return false;
	$class=$testid[0];
	if (is_numeric($class))
		{
		// test ID will stay the same
		$class="L";
		$anytestid=$testid;
		}
	else
		{
		//$testid=substr($testid,1); // as it will here also so direct use to graphs can be made
		$anytestid=substr($testid,1); // the classless version
		}
		
	$q="";
	switch($class)
		{
		case "L": // local tests 
			$q="SELECT * FROM fnlocaltest WHERE localtestid=".ss($anytestid)." LIMIT 0,1";
			break;
		case "N": // node-side test
			$q="SELECT * FROM fnnstest WHERE nstestid=".ss($anytestid)." LIMIT 0,1";
			break;
		default:
			return false; // can't lookup this class
		}
		
	if ($q=="") return false;
	
	$r=$this->DB->Query($q);
	
	if (!$row=$this->DB->Fetch_Array($r)) return false;
	
	$row['class']=$class;
	$row['testid']=$testid;
	$row['anytestid']=$anytestid;
	$row['alerttext']=oText($row['alertlevel']);
	$row['lastrundt']=nicedt($row['lastrunx']);
	$row['lastrunago']=dtago($row['lastrunx'],false);
	
	if  ($row['testname']!="")  $row['name']=$row['testname'];
	else
			{
			if ($class=="L")
				{
				$row['name']=lText($row['testtype']); // TODO OTHER TESTS
				if ($row['testparam']!="") $row['name'].=" (".$row['testparam'].")";
				}
			else if ($class=="N")
				{
				if ($row['testdesc']!="") $row['name']=$row['testdesc'];
				else $row['name']=$row['testtype'];
				}
			}
			
	if ($protect&&($class=="L")) // module test protection
		{
		if ($this->Tests->Exists($row['testtype'])) // in the module register
			{
			$this->Tests->Tests[$row['testtype']]->Create();
			$this->Tests->Tests[$row['testtype']]->instance->ProtectOutput($row);
			}
		}
	
	$this->DB->Free($r);
	
	return $row;
	}

	
function DeleteTest($testid)
	{
	if ($testid=="") return false;
	$class=$testid[0];
	if (is_numeric($class))
		{
		// test ID will stay the same
		$class="L";
		$anytestid=$testid;
		}
	else
		{
		$anytestid=substr($testid,1); // the classless version
		}
		
	$q="";
	switch($class)
		{
		case "L": // local tests 
			$q="DELETE FROM fnlocaltest WHERE localtestid=".ss($anytestid);
			break;
		case "N": // node-side test
			$q="DELETE FROM fnnstest WHERE nstestid=".ss($anytestid);
			break;
		default:
			return false; // can't lookup this class
		}
		
	if ($q=="") return false;
	
	$this->DB->Query($q);
	$tests=$this->DB->Affected_Rows();
	
	$rq="DELETE FROM fnrecord WHERE testid=\"".ss($testid)."\"";
	$this->DB->Query($rq);
	$records=$this->DB->Affected_Rows();
	
	$eq="DELETE FROM fneval WHERE testid=\"".ss($testid)."\"";
	$this->DB->Query($eq);
	$eval=$this->DB->Affected_Rows();
	
	$s="Deleted test ".$testid." (".$tests." tests, ".$records." records, ".$eval." evaluators)";
	$this->Event($s,6,"Test","Delete");
	}
	
	
	
function InvalidateTest($testid,$rightnow=false)
	{
	$class=$testid[0];
	if (is_numeric($class)) $class="L";
	else $testid=substr($testid,1);
	if ($rightnow)
		{
		$nextx=time();
		$q="UPDATE ";
		if ($class=="L") $q.="fnlocaltest";
		// other ones here
		
		$q.=" SET nextrunx=".$nextx." WHERE ";
		
		if ($class=="L") $q.="localtestid=".$testid;
		// other ones here
		
		$this->DB->Query($q);
		return true;
		}
	// otherwise use it's interval
	$q="SELECT testinterval FROM ";
	
	if ($class=="L") $q.="fnlocaltest WHERE localtestid=";
	// other ones here
	
	$q.=$testid;
	$r=$this->DB->Query($q);
	if ($row=$this->DB->Fetch_Array($r))
		{
		$this->DB->Free($r);
		$nextx=next_run_x($row['testinterval']);
		$q="UPDATE ";
		if ($class=="L") $q.="fnlocaltest";
		// other ones here
		
		$q.=" SET nextrunx=".$nextx." WHERE ";
		
		if ($class=="L") $q.="localtestid=".$testid;
		// other ones here
		
		$this->DB->Query($q);
		return true;
		}
	return false;
	}		
	
function InvalidateNode($nodeid,$rightnow=false,$testsaswell=false)
	{
	if ($rightnow)
		{
		$nextx=time();
		$q="UPDATE fnnode SET nextrunx=".$nextx." WHERE nodeid=\"".ss($nodeid)."\"";
		$this->DB->Query($q);
		if ($testsaswell)
			{
			$q="UPDATE fnlocaltest SET nextrunx=".$nextx." WHERE nodeid=\"".ss($nodeid)."\"";
			$this->DB->Query($q);
			}
		return true;
		}
	// otherwise set to it's interval
	$q="SELECT testinterval FROM fnnode WHERE nodeid=\"".ss($nodeid)."\"";
	$r=$this->DB->Query($q);
	if ($row=$this->DB->Fetch_Array($r))
		{
		$nextx=next_run_x($row['testinterval']);
		$uq="UPDATE fnnode SET nextrunx=".$nextx." WHERE nodeid=\"".ss($nodeid)."\"";
		$this->DB->Query($uq);
		$this->DB->Free($r);
		if ($testsaswell)
			{
			$uq="UPDATE fnlocaltest SET nextrunx=".$nextx." WHERE nodeid=\"".ss($nodeid)."\"";
			$this->DB->Query($uq);
			}
		return true;
		}
	return false;
	}
	
	
function NodeSide_Pull($nodeid)
	{
	$eventdata=array("nodeid"=>$nodeid,"success"=>false);
	$q="SELECT nsenabled,nspullenabled,nsurl,nskey,nsinterval FROM fnnode WHERE nodeid=\"".ss($nodeid)."\" LIMIT 0,1";
	$r=$this->DB->Query($q);
	if (!$row=$this->DB->Fetch_Array($r)) return false;
	
	$this->DB->Free($r);
	
	$url=$row['nsurl'];
	if ($row['nskey']!="") $url.="?nodekey=".$row['nskey'];
	//echo $url."\n";
	$this->Event("NodeSide_Pull Started for ".$nodeid,10,"Node","Pull");
	
	$xmlobj=new TNodeXML();
	
	$fp=@fopen($url,"r");
	if ($fp<=0)
		{
		$this->Event("Pull Failed URL ".$url,1,"Node","Pull");
		$this->EventHandler("nodeside_pull",$eventdata);
		return false;
		}
	$xml="";
	while (!feof($fp))
		{
		$xml.=fgets($fp,4096);
		}
	if ($xml=="")
		{
		$this->EventHandler("nodeside_pull",$eventdata);
		return false;
		}
	
	//echo $xml;
	
	$result=$xmlobj->Parse($xml);
	
	if ($xmlobj->Error()!="")
		{
		$this->Event("NodeXML Error: ".$xmlobj->Error(),1,"Node","Pull");
		$this->EventHandler("nodeside_pull",$eventdata);
		return false;
		}
	$this->Event("NodeSide_Pull Fetched ".$xmlobj->Tests." tests for ".$nodeid,10,"Node","Pull");
	// Now just to actually process it...
	$eventdata['success']=true;
	$this->EventHandler("nodeside_pull",$eventdata);
	$this->NodeSide_Process($nodeid,$xmlobj);
	return true;
	}	
	
function NodeSide_Process($nodeid,&$xmlobj)
	{ // nodeid + takes a TNodeXML Object
	$alvl=0;
	$this->Event("NodeSide_Process for ".$nodeid,10,"Node","Pull");
	$q="SELECT * FROM fnnstest WHERE nodeid=\"".ss($nodeid)."\"";
	$r=$this->DB->Query($q);
	$tests=array();
	while ($row=$this->DB->Fetch_Array($r))
		{
		$tests[$row['testtype']]=$row;
		if (isset($xmlobj->Catalogue[$row['testtype']]))
			{ // this test is in the DB and catalogue
			$tests[$row['testtype']]['incat']=true;
			if ($row['testenabled']==1) // it is enabled - so we test it
				{
				if ($row['simpleeval']==1) $level=$xmlobj->Catalogue[$row['testtype']]['ALERTLEVEL']; // use provided level
				else $level=nats_eval("N".$row['nstestid'],$xmlobj->Catalogue[$row['testtype']]['VALUE']);
				$dbs="Nodeside ".$row['testtype']." on ".$row['nodeid']." = ".$level;
				if ($level==0) $debuglev=8;
				else if ($level>0) $debuglev=5;
				else $debuglev=2;
				$this->Event($dbs,$debuglev,"Node","Process");
				
				if ($level>$alvl) $alvl=$level;
				
				if ($row['testrecord']==1) // record it
					{
					$testvalue=$xmlobj->Catalogue[$row['testtype']]['VALUE'];
					$testvalue=str_replace(",",".",$testvalue);
					if (!is_numeric($testvalue)) $testvalue=0;
					$iq="INSERT INTO fnrecord(testid,alertlevel,recordx,nodeid,testvalue) VALUES(";
					$iq.="\"N".$row['nstestid']."\",".$level.",".time().",\"".$row['nodeid']."\",".$testvalue.")";
					$this->DB->Query($iq);
					if ($this->DB->Affected_Rows()<=0)
						$this->Event("Nodeside ".$row['testtype']." Failed to Record",1,"Node","Process");
					}
					
				// We don't do any alerting here - the tester will do that for us!
				$uq="UPDATE fnnstest SET lastrunx=".time().",lastvalue=\"".ss($xmlobj->Catalogue[$row['testtype']]['VALUE'])."\",alertlevel=".$level." ";
				$uq.="WHERE nstestid=".$row['nstestid'];
				$this->DB->Query($uq);
				if ($this->DB->Affected_Rows()<=0)
						$this->Event("Nodeside ".$row['testtype']." Failed to Update or Same Values",5,"Node","Process");
				
				}
					
			// check to see if the desc has changed
			if ($row['testdesc']!=$xmlobj->Catalogue[$row['testtype']]['DESC'])
				{
				$duq="UPDATE fnnstest SET testdesc=\"".ss($xmlobj->Catalogue[$row['testtype']]['DESC'])."\" WHERE nstestid=".$row['nstestid'];
				$this->DB->Query($duq);
				}
				
			}
		else
			{
			// test in the DB but NOT in the catalogue
			//$xmlobj->Catalogue[$row['testtype']]['incat']=false;
			if ($row['testenabled']==1)
				{ // enabled so shown in lists etc
				// Update it to show failed status
				$this->Event("No nodeside data for test N".$row['nstestid'],3,"Node","Process");
				$uq="UPDATE fnnstest SET alertlevel=2,lastvalue=-1 WHERE nstestid=".$row['nstestid'];
				$this->DB->Query($uq);
				$alvl=2;
				if ($row['testrecord']==1) // record it
					{
					$testvalue=-1;
					$iq="INSERT INTO fnrecord(testid,alertlevel,recordx,nodeid,testvalue) VALUES(";
					$iq.="\"N".$row['nstestid']."\",2,".time().",\"".$row['nodeid']."\",".$testvalue.")";
					$this->DB->Query($iq);
					if ($this->DB->Affected_Rows()<=0)
						$this->Event("Nodeside ".$row['testtype']." Failed to Record",1,"Node","Process");
					}
				}
			else // not enabled so simply delete
				{
				$this->DeleteTest("N".$row['nstestid']);
				}
			}
		}
	$this->DB->Free($r);
	
	
	// and finally we look for new tests i.e. in the cat but not in the DB
	foreach($xmlobj->Catalogue as $val)
		{
		$key=$val['NAME'];
		if (!isset($tests[$key])) // not in the DB
			{
			$q="INSERT INTO fnnstest(nodeid,testtype,testdesc,lastvalue,lastrunx,alertlevel) ";
			$q.="VALUES(\"".ss($nodeid)."\",\"".$key."\",\"".ss($val['DESC'])."\",\"".ss($val['VALUE'])."\",".time().",".ss($val['ALERTLEVEL']).")";
			//echo $q."<br>";
			$this->DB->Query($q);
			}
		}
		
	$eventdata=array("nodeid"=>$nodeid,"alertlevel"=>$alvl);
	$this->EventHandler("nodeside_process",$eventdata);
	
	
	}
	
	
function AddEventHandler($event,$function)
{
	if (!isset($this->EventHandlers[$event])) $this->EventHandlers[$event]=array();
	$this->EventHandlers[$event][]=$function;
}

function EventHandler($event,$data)
{
	$res=false;
	if ( isset($data) && is_array($data) ) $data['event']=$event;
	
	if (isset($this->EventHandlers[$event])) // handler(s) exist
	{
	for($a=0; $a<count($this->EventHandlers[$event]); $a++)
		{
		if (function_exists($this->EventHandlers[$event][$a]))
			{
			$this->Event("Event ".$event." -> ".$this->EventHandlers[$event][$a],6,"Event","Handler");
			if($this->EventHandlers[$event][$a]($data)) $res=true; // persist true only
			}
		else
			{
			$t="Illegal Handler ".$this->EventHandlers[$event][$a]." for ".$event;
			$this->Event($t,2,"Event","Handler");
			//return false;
			}
		}
	}
	else return $res;
}

function StripGPC($data)
{
	if (get_magic_quotes_gpc()) return stripslashes($data);
	else return $data;
}

function PageError($code,$desc)
{
	$this->PageErrors[]=array( "code" => $code, "desc" => $desc );
}

function isUserRestricted($username)
{
	if (!isset($this->userdata[$username]))
	{
		$q="SELECT * FROM fnuser WHERE username=\"".ss($username)."\" LIMIT 0,1";
		$r=$this->DB->Query($q);
		if ($u=$this->DB->Fetch_Array($r))
			$this->userdata[$username]=$u;
		else
			return false;
		$this->DB->Free($r);
	}

	// If user is a normal user & restricted then YES
	if ($this->userdata[$username]['userlevel']<2 && $this->userdata[$username]['grouplock'])
		return true;
	return false;
}

function isUserAllowedGroup($username, $groupid)
{
	if (!$this->isUserRestricted($username)) return true;

	if (!isset($this->usergrouplock[$username]))
	{
		$q="SELECT * FROM fngrouplock WHERE username=\"".ss($username)."\"";
		$r=$this->DB->Query($q);
		$this->usergrouplock[$username]=array();
		while($row=$this->DB->Fetch_Array($r))
			$this->usergrouplock[$username][]=$row['groupid'];
	}

	if (in_array($groupid,$this->usergrouplock[$username]))
		return true;
	return false;
}

function isUserAllowedNode($username, $nodeid)
{
	if (!$this->isUserRestricted($username)) return true;

	$q="SELECT groupid FROM fngrouplink WHERE nodeid=\"".ss($nodeid)."\"";
	$r=$this->DB->Query($q);
	while ($row=$this->DB->Fetch_Array($r))
	{
		if ($this->isUserAllowedGroup($username, $row['groupid']))
			return true;
	}

	return false;
}
	
}
?>
