<?php // nats.cfg.inc.php -- config module for NATS
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

class TNATS_Cfg
	{
	var $loaded=false;
	var $data=array();
	var $default="";
	
	function Load($nats_db)
		{
		$q="SELECT * FROM fnconfig";
		$r=$nats_db->Query($q);
		while ($row=$nats_db->Fetch_Array($r))
			{
			$this->data[$row['fnc_var']]=$row['fnc_val'];
			//echo $row['fnc_var']."=".$row['fnc_val']."\n<br>";
			}
		$nats_db->Free($r);
		}
		
	function Get($var,$def="")
		{
		if (isset($this->data[$var])) return $this->data[$var];
		return $def;
		}
		
	function DumpToScreen()
		{
		$keys=array_keys($this->data);
		foreach($keys as $key)
			{
			echo $key."=".$this->data[$key]."<br>\n";
			}
		}
	function Set($var,$val,$perm=true)
		{
		$this->data[$var]=$val;
		if ($perm)
			{
			global $NATS;
			$q="UPDATE fnconfig SET fnc_val=\"".ss($val)."\" WHERE fnc_var=\"".ss($var)."\"";
			$NATS->DB->Query($q);
			if ($NATS->DB->Affected_Rows()<=0) // not already existing
				{
				$q="INSERT INTO fnconfig(fnc_var,fnc_val) VALUES(\"".ss($var)."\",\"".ss($val)."\")";
				mysql_query($q);
				}
			}
		}
	}