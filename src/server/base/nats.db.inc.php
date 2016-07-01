<?php // nats.db.inc.php -- nats db module class
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
along with Foobar.  If not, see www.gnu.org/licenses

For more information see www.purplepixie.org/freenats
-------------------------------------------------------------- */

function ss($s) // safestring
{
	global $NATS;
	return $NATS->DB->SafeString($s);
}

class TNATS_DB
	{
	var $connected=false;
	var $sql=0;
	
	var $LastError=0;
	var $LastErrorString="";
	
	function Connect()
		{
		global $fnCfg;
		$this->sql=mysqli_connect($fnCfg['db.server'],$fnCfg['db.username'],$fnCfg['db.password'])
			or die("Cannot connect to MySQL server");
		mysqli_select_db($this->sql,$fnCfg['db.database'])
			or die("Cannot select MySQL database");
		$this->connected=true;
		return $this->sql;
		}
		
	function Disconnect()
		{
		mysqli_close($this->sql);
		$this->sql=0;
		$this->connected=false;
		}

	function SafeString($s)
	{
		return mysqli_real_escape_string($this->sql,$s);
	}
		
	function Query($query,$debugerror=true)
		{
		global $NATS;
		if (!$this->connected) return -1;
		$result=mysqli_query($this->sql,$query);
		if ($debugerror)
			{
			// persist the last error state
			$this->LastError=mysqli_errno($this->sql);
			if ($this->LastError>0)
				{
				$this->ErrorString=mysqli_error($this->sql)." (".mysqli_errno($this->sql).")";
				}
			else $this->ErrorString="";
			}
			
		if (mysqli_errno($this->sql)>0)
			{
			$err=mysqli_error($this->sql)." (".mysqli_errno($this->sql).")";
			if (isset($NATS)&&$debugerror)
				{
				$NATS->Event("Query Failed: ".$query,2,"DB","Query");
				$NATS->Event("Query Error: ".$err,2,"DB","Query");
				}
			}
		return $result;
		}
		
	function Free(&$result)
		{
		mysqli_free_result($result);
		}
		
	function Fetch_Array(&$result)
		{
		return mysqli_fetch_array($result);
		}
		
	function Affected_Rows()
		{
		return mysqli_affected_rows($this->sql);
		}
		
	function Insert_Id()
		{
		return mysqli_insert_id($this->sql);
		}
		
	function Num_Rows(&$result)
		{
		return mysqli_num_rows($result);
		}
		
	function Error()
		{
		if ($this->LastError==0) return false;
		return true;
		}
		
	function Error_Number()
		{
		return $this->LastError;
		}
		
	function Error_String()
		{
		return $this->ErrorString;
		}
	}