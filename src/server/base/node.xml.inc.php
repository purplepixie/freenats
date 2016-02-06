<?php // node.xml.inc.php
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

/* Modified 24/08/2009 for Added Debugging - may want to remove from
   main release dependent on performance impact */

/* Modified again 25/08/2009 - def don't want these mods included in
   the main release */

class TNodeXML
{
var $Catalogue=array();
var $Init=false;
var $Elements=0;
var $Tests=0;
var $Header=array();
var $LastError="";

var $in_freenats=false;
var $in_header=false;
var $in_test=false;
var $depth=0;
var $last_element="";
var $cur_testname="";

var $debug_log=false; //disabled but keep in for the moment

function startElement($parser, $name, $attrs)
{
global $NATS;
if ($this->debug_log)
	$NATS->Event("Start Element: ".$name,10,"NodeXML","StartE");
if ($name=="FREENATS-DATA") 
	{
	$this->in_freenats=true;
	if ($this->debug_log)
		$NATS->Event("in_freenats set to true",10,"NodeXML","StartE");
	}
if (!$this->in_freenats) return 0;

if ($this->debug_log)
	$NATS->Event("startE passed in_freenats true",10,"NodeXML","StartE");

if ($name=="TEST")
	{
	$this->in_test=true;
	$this->Tests++;
	$this->cur_testname=$attrs['NAME'];
	if ($this->debug_log)
		$NATS->Event("Test Element: ".$this->cur_testname,10,"NodeXML","TestE");
	}
else if ($name=="HEADER") $in_header=true;
$this->last_element=$name;
$this->depth++;
$this->Elements++;
}

function endElement($parser, $name)
{
global $NATS;
$this->depth--;
if ($name=="TEST") $in_test=false;
else if ($name=="HEADER") $in_header=false;
else if ($name=="FREENATS-DATA")
	{
	$in_freenats=false;
	if ($this->debug_log)
		$NATS->Event("endE in_freenats set to false",10,"NodeXML","EndE");
	}
if ($this->debug_log)
	$NATS->Event("End Element: ".$name,10,"NodeXML","EndE");
}

function charData($parser,$data)
{
global $NATS;
if ($this->debug_log) $NATS->Event("Char Data Called",10,"NodeXML","CharData");
if (!$this->in_freenats) 
	{
	if ($this->debug_log)
		$NATS->Event("Char Data in_freenats FALSE",10,"NodeXML","CharData");
	return 0;
	}
if ($this->debug_log)
	{
	$NATS->Event("Char Data in FreeNATS",10,"NodeXML","CharData");
	$NATS->Event("Untrimmed Data: ".$data,10,"NodeXML","CharData");
	}

$data=trim($data);
if ($data!="")
	{
	if ($this->debug_log)
		$NATS->Event("Data: ".$data,10,"NodeXML","CharData");
		
	if ($this->in_test)
		{
		$this->Catalogue[$this->cur_testname][$this->last_element]=$data;
		}
	else if ($this->in_header)
		$this->Header[$this->last_element]=$data;
	}
}

function Error()
{
return $this->LastError;
}

function Parse($xml)
{
if (get_magic_quotes_gpc()) $xml=stripslashes($xml);
$this->Init=true;
$parser=xml_parser_create();
xml_set_element_handler($parser,Array( $this, "startElement" ),
	Array( $this, "endElement" ) );
xml_set_character_data_handler($parser,Array( $this, "charData" ));

$return=true;

if (!xml_parse($parser,$xml,true))
	{
	$this->LastError="XML Error ".xml_error_string(xml_get_error_code($parser));
	$this->LastError.=" at line ".xml_get_current_line_number($parser);
	if ($debug_log) $NATS->Event($this->LastError,5,"NodeXML","Parse");
	$return=false;
	}

xml_parser_free($parser);

if ($return) return $this->Catalogue;
else return 0;
}

function ParseFile($xmlfile)
{
$fp=fopen($xmlfile,"r")
 or die("Could not open XML file ".$xmlfile);
$data="";
while (!feof($fp))
	$data.=fgets($fp,4096);
fclose($fp);
return $this->Parse($data);
}


}
?>