<?php
/* -------------------------------------------------------------
This file is part of FreeNATS
FreeNATS is (C) Copyright 2008-2023 PurplePixie Systems
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

class NATS_RSS
{
	var $NATS = false;

	function __construct()
	{
		global $NATS;
		$this->NATS = &$NATS;
	}

	function Create($name)
	{
		mt_srand(microtime() * 1000000);
		$allow = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$alen = strlen($allow);
		$key = "";
		$len = 32;
		for ($a = 0; $a < $len; $a++) {
			$key .= $allow[mt_rand(0, $alen - 1)];
		}
		$q = "INSERT INTO fnrssfeed(feedkey,feedname, feedtype, feedrange, rangeopt) "
			. "VALUES(\"" . $key . "\",\"" . ss($name) . "\",\"all\""
			. ",\"xdays\",30)";
		$this->NATS->DB->Query($q);

		return $this->NATS->DB->Insert_Id();
	}

	function Delete($id)
	{
		$q = "DELETE FROM fnrssfeed WHERE feedid=" . ss($id);
		$this->NATS->DB->Query($q);
	}

	function GetTypes()
	{
		return array(
			"all" => $this->NATS->Lang->Item("all.enabled.nodes"),
			"node" => $this->NATS->Lang->Item("one.node"),
			"group" => $this->NATS->Lang->Item("group")
		);
	}

	function GetRanges()
	{
		return array(
			"xdays" => $this->NATS->Lang->Item("last.x.days"),
			"xalerts" => $this->NATS->Lang->Item("last.x.alerts"),
			"alerts" => $this->NATS->Lang->Item("current.alerts"),
			"alertnode" => $this->NATS->Lang->Item("last.alerts.node")
		);
	}

	function GetFeeds($id = 0)
	{
		$q = "SELECT * FROM fnrssfeed";
		if ($id != 0)
			$q .= " WHERE feedid=" . ss($id) . " LIMIT 0,1";

		$out = array();

		$r = $this->NATS->DB->Query($q);

		$types = $this->GetTypes();
		$ranges = $this->GetRanges();

		while ($row = $this->NATS->DB->Fetch_Array($r)) {
			$out[] = $row;
		}

		$this->NATS->DB->Free($r);

		return $out;
	}

	function GetFeed($id)
	{
		$feeds = $this->GetFeeds($id);
		if (count($feeds) > 0)
			return $feeds[0];
		else
			return $feeds;
	}

	function SaveFeed($id, $data)
	{
		$q = "UPDATE fnrssfeed SET ";
		$first = true;

		foreach ($data as $field => $value) {
			if ($first)
				$first = false;
			else
				$q .= ",";
			$q .= ss($field) . "=\"" . ss($value) . "\"";
		}
		$q .= " WHERE feedid=" . ss($id);

		$this->NATS->DB->Query($q);

		if ($this->NATS->DB->Affected_Rows() <= 0)
			return false;
		else
			return true;
	}

	function GetIdFromCompound($compound)
	{
		$parts = explode("-", $compound);
		$key = $parts[0];
		$id = $parts[1];

		$q = "SELECT feedid FROM fnrssfeed WHERE feedid=" . ss($id) . " AND feedkey=\"" . ss($key) . "\" LIMIT 0,1";
		$r = $this->NATS->DB->Query($q);

		if ($row = $this->NATS->DB->Fetch_Array($r)) {
			$this->NATS->DB->Free($r);
			return $row['feedid'];
		} else
			return false;
	}

	function GetCompound($id, $key = "")
	{
		if ($key == "") {
			$feed = $this->GetFeed($id);
			$key = $feed['feedkey'];
		}
		return $key . "-" . $id;
	}

	function GetURL($id, $key = "")
	{
		$compound = $this->GetCompound($id, $key);
		return GetAbsolute("rss.php?feed=" . $compound);
	}

	function Item($title, $link, $published, $desc)
	{
		$out = "<item>\n"
			. "<title><![CDATA[" . $title . "]]></title>\n"
			. "<link><![CDATA[" . $link . "]]></link>\n"
			. "<guid><![CDATA[" . $link . "]]></guid>\n"
			. "<pubDate><![CDATA[" . $published . "]]></pubDate>\n"
			. "<description><![CDATA[" . $desc . "]]></description>\n"
			. "</item>\n";
		return $out;
	}

	function Render($id)
	{
		global $NATS;
		$feed = $this->GetFeed($id);
		if (count($feed) <= 0)
			return false;
		$out = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
		$out .= "<rss version=\"2.0\" xmlns:atom=\"http://www.w3.org/2005/Atom\">\n";
		$out .= "<channel>\n";
		$out .= "<title><![CDATA[";
		if ($feed['feedname'] != "")
			$out .= $feed['feedname'];
		else
			$out .= "FreeNATS RSS Feed";
		$out .= "]]></title>\n";
		$me = GetAbsolute("rss.php?feed=" . $_REQUEST['feed']);
		$out .= "<link>" . $me . "</link>\n";
		$out .= "<atom:link href=\"" . $me . "\" rel=\"self\" type=\"application/rss+xml\" />\n";
		$out .= "<description>FreeNATS RSS Feed</description>\n";
		$out .= "<lastBuildDate>" . date("D, d M Y H:i:s") . " GMT</lastBuildDate>\n";
		$out .= "<language>en-us</language>\n";

		switch ($feed['feedtype']) {
			case "all":
				$nodes = $this->NATS->GetNodes();
				break;
			case "node":
				$nodes = array();
				$nodes[]['nodeid'] = $feed['typeopt'];
				break;
			case "group":
				$nodes = array();
				$q = "SELECT nodeid FROM fngrouplink WHERE groupid=" . ss($feed['typeopt']);
				$r = $this->NATS->DB->Query($q);
				while ($row = $this->NATS->DB->Fetch_Array($r)) {
					$nodes[] = $NATS->GetNode($row['nodeid']);
				}
				$this->NATS->DB->Free($r);
				break;
			default:
				$nodes = array();
		}

		$inlist = "";
		$first = true;
		foreach ($nodes as $node) {
			if ($first)
				$first = false;
			else
				$inlist .= ",";
			$inlist .= "\"" . ss($node['nodeid']) . "\"";
		}

		$alerts = array();
		switch ($feed['feedrange']) {
			case "xdays":
				if (!is_numeric($feed['rangeopt']))
					$days = 30;
				else
					$days = $feed['rangeopt'];
				$secs = $days * 24 * 60 * 60;
				$from = time() - $secs;
				$q = "SELECT * FROM fnalert WHERE (closedx=0 OR closedx>" . $from . ") AND nodeid IN (" . $inlist . ") ORDER BY LENGTH(closedx) ASC, closedx DESC";
				break;

			case "xalerts":
				if (!is_numeric($feed['rangeopt']))
					$acount = 30;
				else
					$acount = $feed['rangeopt'];
				$q = "SELECT * FROM fnalert WHERE nodeid IN(" . $inlist . ") ORDER BY LENGTH(closedx) ASC, closedx DESC LIMIT 0," . $acount;
				break;

			case "alerts":
				$q = "SELECT * FROM fnalert WHERE closedx=0 AND nodeid IN(" . $inlist . ")";
				break;

			case "alertnode":
				$q = array();
				foreach ($nodes as $node) {
					$q[] = "SELECT * FROM fnalert WHERE nodeid=\"" . ss($node['nodeid']) . "\" ORDER BY LENGTH(closedx) ASC, closedx DESC LIMIT 0,1";
				}
				break;
		}
		if (!is_array($q))
			$q = array($q);
		foreach ($q as $query) {
			$r = $this->NATS->DB->Query($query);
			while ($row = $this->NATS->DB->Fetch_Array($r)) {
				$alerts[] = $row;
			}
			$this->NATS->DB->Free($r);
		}

		foreach ($alerts as $alert) {
			$title = $alert['nodeid'] . " " . $this->NATS->Lang->Item("alert");
			if ($alert['closedx'] != 0)
				$title .= " (" . $this->NATS->Lang->Item("closed") . ")";
			else
				$title .= " (" . $this->NATS->Lang->Item("open") . ")";
			$link = GetAbsolute("node.php?nodeid=" . $alert['nodeid']);
			if ($alert['closedx'] == 0)
				$pubtime = $alert['openedx'];
			else
				$pubtime = $alert['closedx'];
			$pub = date("D, d M Y H:i:s", $pubtime) . " GMT";

			$q = "SELECT logentry FROM fnalertlog WHERE alertid=" . $alert['alertid'] . " ORDER BY alid ASC LIMIT 0,1";
			$r = $this->NATS->DB->Query($q);
			if ($row = $this->NATS->DB->Fetch_Array($r))
				$desc = $row['logentry'];
			else
				$desc = "";
			$this->NATS->DB->Free($r);

			$out .= $this->Item($title, $link, $pub, $desc);
		}

		$out .= "</channel>\n</rss>\n";

		return $out;
	}
}
