<?php
/* -------------------------------------------------------------
This file is part of FreeNATS

FreeNATS is (C) Copyright 2008-2011 PurplePixie Systems

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


// FreeNATS Bulk Importer
// Usage php import.php filename.xml

echo "FreeNATS Bulk Importer - www.purplepixie.org/freenats\n";

if ($argc < 2) {
	echo "Usage: php import.php filename.xml [--live] [--debug]\n\n";
	exit();
}




$live = false;
$debug = false;
for ($a = 1; $a < $argc; $a++) {
	if ($argv[$a] == "--live") $live = true;
	else if ($argv[$a] == "--debug") $debug = true;
	else $file = $argv[$a];
}

echo "Importing File: " . $file . " => Live Import: ";
if ($live) echo "Yes";
else echo "No";
echo "\n\n";


class TImportXML
{
	var $Init = false;
	var $Elements = 0;
	var $Nodes = 0;
	var $NodeList = array();
	var $Localtests = 0;
	var $LocaltestList = array();

	var $NodeDefaults = array();
	var $LocaltestDefaults = array();

	var $in_freenats = false;
	var $in_node = false;
	var $in_localtest = false;
	var $in_nodedefault = false;
	var $in_localtestdefault = false;
	var $in_test = false;
	var $depth = 0;
	var $last_element = "";
	var $cur_id = "";

	function startElement($parser, $name, $attrs)
	{
		if ($name == "FREENATS-DATA") $this->in_freenats = true;
		if (!$this->in_freenats) return 0;

		if ($name == "NODE") {
			$this->in_node = true;
			$this->Nodes++;
			$this->NodeList[$attrs['NODEID']] = $this->NodeDefaults;
			$this->cur_id = $attrs['NODEID'];
		} else if ($name == "DEFAULT") {
			if ($attrs['TYPE'] == "node") {
				echo "Node Default Section Starting\n";
				$this->in_nodedefault = true;
			} else if ($attrs['TYPE'] == "localtest") {
				echo "Localtest Default Section Starting\n";
				$this->in_localtestdefault = true;
			}
		} else if ($name == "LOCALTEST") {
			$this->in_localtest = true;
			//echo "In Local Test\n";
			$this->LocaltestList[$this->Localtests] = $this->LocaltestDefaults;
			$this->Localtests;
		}
		$this->last_element = $name;
		$this->depth++;
		$this->Elements++;
	}

	function endElement($parser, $name)
	{
		$this->depth--;
		if ($name == "LOCALTEST") {
			//echo "Finished Localtest\n";
			$this->in_localtest = false;
			$this->Localtests++;
		} else if ($name == "NODE") $this->in_node = false;
		else if ($name == "DEFAULT") {
			$this->in_nodedefault = false;
			$this->in_localtestdefault = false;
			echo "Default Section End\n\n";
		} else if ($name == "FREENATS-DATA") $in_freenats = false;
	}

	function charData($parser, $data)
	{
		if (!$this->in_freenats) return 0;
		$data = trim($data);
		if ($data != "") {
			if ($this->in_node) {
				$this->NodeList[$this->cur_id][strtolower($this->last_element)] = $data;
				echo "Node Data: " . $data . "\n";
			} else if ($this->in_nodedefault) {
				echo " Default: " . strtolower($this->last_element) . " => " . $data . "\n";
				$this->NodeDefaults[strtolower($this->last_element)] = $data;
			} else if ($this->in_localtestdefault) {
				echo " Default: " . strtolower($this->last_element) . " => " . $data . "\n";
				$this->LocaltestDefaults[strtolower($this->last_element)] = $data;
			} else if ($this->in_localtest) {
				$test = $this->Localtests;
				//echo "***".$data;
				$this->LocaltestList[$test][strtolower($this->last_element)] = $data;
			}
			//else echo $data;

		}
	}

	function Error()
	{
		return $this->LastError;
	}

	function Parse($xml)
	{
		$this->Init = true;
		if (get_magic_quotes_gpc()) $xml = stripslashes($xml);
		$parser = xml_parser_create();
		xml_set_element_handler(
			$parser,
			array($this, "startElement"),
			array($this, "endElement")
		);
		xml_set_character_data_handler($parser, array($this, "charData"));

		$return = true;

		if (!xml_parse($parser, $xml, true)) {
			$this->LastError = "XML Error " . xml_error_string(xml_get_error_code($parser));
			$this->LastError .= " at line " . xml_get_current_line_number($parser);
			$return = false;
		}

		xml_parser_free($parser);

		return true;
	}

	function ParseFile($xmlfile)
	{
		$fp = fopen($xmlfile, "r")
			or die("Could not open XML file " . $xmlfile);
		$data = "";
		while (!feof($fp))
			$data .= fgets($fp, 4096);
		fclose($fp);
		return $this->Parse($data);
	}
}


$xml = new TImportXML();
if ($xml->ParseFile($file) === false) {
	echo "Error Encountered!\n";
	echo $xml->LastError;
	echo "\n\n";
	exit();
}

echo "Nodes: " . $xml->Nodes . "     Local Tests: " . $xml->Localtests . "\n";
if ($live) {
	echo "Starting FreeNATS for live import... ";
	@include("include.php");
	if (!isset($BaseDir)) $BaseDir = "../base/";

	//require($BaseDir."nats.php"); -- BID 263
	$NATS->Start();
	echo "Ok\n";
}
foreach ($xml->NodeList as $nodeid => $node) {

	echo "NodeID: " . $nodeid . " - Parsing Properties...\n";
	$fields = "(";
	$values = "(";
	$first = true;
	foreach ($node as $key => $val) {
		if ($first) $first = false;
		else {
			$fields .= ",";
			$values .= ",";
		}
		$fields .= $key;
		$values .= "\"" . $val . "\"";
		echo "  " . $key . " => " . $val . "\n";
	}
	$fields .= ")";
	$values .= ")";

	$q = "INSERT INTO fnnode" . $fields . " VALUES" . $values;
	if ($debug) echo "  SQL: " . $q . "\n";

	if ($live) { // actual import
		$NATS->DB->Query($q);
		if ($NATS->DB->Error()) {
			echo "Failed: SQL Error: " . $NATS->DB->Error_String() . "\n";
			$xml->NodeList[$nodeid]['import_success'] = false;
		} else if ($NATS->DB->Affected_Rows() <= 0) {
			echo "Failed: SQL INSERT Failed: Duplicate or Blank?\n";
			$xml->NodeList[$nodeid]['import_success'] = false;
		} else {
			echo "Succeeded: SQL INSERT Success\n";
			$xml->NodeList[$nodeid]['import_success'] = true;
		}
	} else { // dummy run
		echo "  Live Import: No\n";
		$xml->NodeList[$nodeid]['import_success'] = true;
	}
	echo "\n";
}

foreach ($xml->LocaltestList as $test) {
	//print_r($test);
	//exit();
	echo "Test " . $test['testtype'] . " for " . $test['nodeid'] . "\n";
	$process = false;
	if (isset($xml->NodeList[$test['nodeid']])) {
		if (isset($xml->NodeList[$test['nodeid']]['import_success']) && $xml->NodeList[$test['nodeid']]['import_success']) {
			echo "Processing Test\n";
			$process = true;
		} else {
			echo "Skipping Test: Node failed to import in this data\n";
		}
	} else {
		echo "WARNING: Node not created in this script for this test\n";
		$process = true;
	}
	if ($process) {
		$fields = "(";
		$values = "(";
		$first = true;
		foreach ($test as $key => $val) {
			if ($first) $first = false;
			else {
				$fields .= ",";
				$values .= ",";
			}
			$fields .= $key;
			$values .= "\"" . $val . "\"";
			echo "  " . $key . " => " . $val . "\n";
		}
		$fields .= ")";
		$values .= ")";
		$q = "INSERT INTO fnlocaltest" . $fields . " VALUES" . $values;
		if ($debug) echo "  SQL: " . $q . "\n";
		if ($live) { // actual import
			$NATS->DB->Query($q);
			if ($NATS->DB->Error()) {
				echo "Failed: SQL Error: " . $NATS->DB->Error_String() . "\n";
			} else if ($NATS->DB->Affected_Rows() <= 0) {
				echo "Failed: SQL INSERT Failed: Duplicate or Blank?\n";
			} else {
				echo "Succeeded: SQL INSERT Success\n";
			}
		}
	}
	echo "\n";
}

if ($live) $NATS->Stop();
