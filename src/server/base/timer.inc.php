<?php // timer.inc.php
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

class TFNTimer
{
var $StartSecs;
var $StartMSecs;
var $FinishSecs;
var $FinishMSecs;
var $Elapsed;
var $SafeElapsed;

function Start()
	{
	$timeString=microtime();  // 0.000000 0000000
	$timeArray=explode(" ",$timeString);
	$this->StartSecs=$timeArray[1];
	$this->StartMSecs=$timeArray[0];
	}
	
function Stop()
	{
	$timeString=microtime();
	$timeArray=explode(" ",$timeString);
	$this->FinishSecs=$timeArray[1];
	$this->FinishMSecs=$timeArray[0];
	
	$elapSecs=$this->FinishSecs-$this->StartSecs;
	//$newFinish=$elapSecs.substr($this->FinishMSecs,1,128);
	$newFinish=$elapSecs+$this->FinishMSecs;
	
	$this->Elapsed=$newFinish-$this->StartMSecs;
	$this->SafeElapsed=round($this->Elapsed,3);
	if ($this->SafeElapsed<=0) $this->SafeElapsed="0.0001";
	
	return $this->SafeElapsed;
	}
}

?>