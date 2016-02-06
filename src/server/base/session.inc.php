<?php // session.inc.php -- NATS Session Manager
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

class TNATS_Session
{
var $auth=false;
var $username="";
var $userlevel="";
var $sessionid=0;
var $sessionkey="";
var $ipaddress="";

var $schrs="abcdefghijklmnopqrstuvwxyz0123456789";
var $slen=120;

function Create(&$db,$uname,$pword)
	{
	$q="SELECT userlevel FROM fnuser WHERE username=\"".ss($uname)."\" AND ";
	$q.="password=MD5(\"".ss($pword)."\") LIMIT 0,1";
	$r=$db->Query($q);
	if ($row=$db->Fetch_Array($r))
		{ // success
		$db->Free($r);
		return $this->Register($db,$uname);
		}
	return false;
	}
	
function Register(&$db,$uname)
	{
	$q="SELECT userlevel FROM fnuser WHERE username=\"".ss($uname)."\"";
	$r=$db->Query($q);
	if (!$row=$db->Fetch_Array($r)) return false; // invalid user
	$db->Free($r);
	mt_srand(microtime()*100000);
	for ($a=0; $a<$this->slen; $a++)
		{
		$this->sessionkey.=$this->schrs[mt_rand(0,strlen($this->schrs)-1)];
		}
	$q="INSERT INTO fnsession(sessionkey,ipaddress,username,startx,updatex,userlevel) ";
	$q.="VALUES(\"".$this->sessionkey."\",\"".ss($_SERVER['REMOTE_ADDR'])."\",\"".ss($uname)."\",";
	$q.=time().",".time().",".$row['userlevel'].")";
	$db->Query($q);
	if ($db->Affected_Rows()<=0) die("Failed to create session record");
	$this->username=$uname;
	$this->userlevel=$row['userlevel'];
	$this->sessionid=$db->Insert_Id();
	$this->ipaddress=$_SERVER['REMOTE_ADDR'];
	$this->auth=true;
	setcookie("fn_sid",$this->sessionid);
	setcookie("fn_skey",$this->sessionkey);
	return $this->sessionid;	
	}

function Check(&$db,$timeskip=false) // timeskip (1.02.1) avoids checking for or setting time (for live monitor)
	{
	if (!isset($_COOKIE['fn_sid'])) return false;
	if (!isset($_COOKIE['fn_skey'])) return false;
	
	$q="SELECT username,userlevel FROM fnsession WHERE ";
	$q.="sessionid=".ss($_COOKIE['fn_sid'])." AND sessionkey=\"".ss($_COOKIE['fn_skey'])."\" AND ";
	$q.="ipaddress=\"".ss($_SERVER['REMOTE_ADDR'])."\"";
	if (!$timeskip) $q.="AND updatex>".(time()-(30*60));
	$q.=" LIMIT 0,1";
	$r=$db->Query($q);
	if (!$row=$db->Fetch_Array($r)) return false;
	
	$this->sessionid=$_COOKIE['fn_sid'];
	$this->sessionkey=$_COOKIE['fn_skey'];
	$this->username=$row['username'];
	$this->userlevel=$row['userlevel'];
	$this->ipaddress=$_SERVER['REMOTE_ADDR'];
	$this->auth=true;
	
	if (!$timeskip)
		{
		$q="UPDATE fnsession SET updatex=".time()." WHERE sessionid=".ss($this->sessionid);
		$db->Query($q);
		}
	
	return true;
	}
	
function Destroy($db)
	{
	$q="DELETE FROM fnsession WHERE sessionid=".ss($this->sessionid)." AND sessionkey=\"".ss($this->sessionkey)."\"";
	$db->Query($q);
	setcookie("fn_sid","");
	setcookie("fn_skey","");
	return true;
	}
	
}