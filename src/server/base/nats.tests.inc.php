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

class TNATS_Test
{
var $type="";
var $instance=false;
var $class="";
var $name="";
var $revision=-1;
var $additional="";
var $parameters=false;

function TNATS_Test($type,$class,$parameters=false,$name="",$revision=0,$additional="")
	{
	$this->type=$type;
	$this->class=$class;
	if ($name=="") $this->name=$type;
	else $this->name=$name;
	$this->revision=$revision;
	$this->additional=$additional;
	if ($parameters===false) $this->parameters=array();
	else $this->parameters=$parameters;
	}
	
function Create()
	{
	if ($this->instance===false) // doesn't exist
		{
		$this->instance=new $this->class();
		}
	return $this->instance;
	}
}

class TNATS_Tests
{
var $count=0;
var $Tests=array();
var $QuickList=array(); // quick list to save doing it dynamically each time
var $UnitList=array(); // Unit list for easy reference

function Register($type,$class,$parameters=false,$name="",$revision=0,$additional="")
	{
	if ($name=="") $name=$type;
	$this->Tests[$type]=new TNATS_Test($type,$class,$parameters,$name,$revision,$additional);
	$this->QuickList[$type]=$name;
	$this->count++;
	}
	
function Get($type)
	{
	if (isset($this->Tests[$type])) return $this->Tests[$type];
	return -1;
	}
	
function QuickList()
	{
	return $this->QuickList;
	}
	
function Exists($type)
	{
	if (isset($this->Tests[$type])) return true;
	return false;
	}
	
function SetUnits($type,$long,$short="")
	{
	if (!isset($this->UnitList[$type]))
		$this->UnitList[$type]=array();
		
	$this->UnitList[$type]['long']=$long;
	$this->UnitList[$type]['short']=$short;
	}
	
function Units($type,$long=true)
	{
	if (!isset($this->UnitList[$type])) return "";
	if ($long) return $this->UnitList[$type]['long'];
	else return $this->UnitList[$type]['short'];
	}
	

}
?>