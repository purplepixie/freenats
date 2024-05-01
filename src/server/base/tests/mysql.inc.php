<?php // mysql.inc.php -- MySQL Test
/* -------------------------------------------------------------
This file is part of FreeNATS

FreeNATS is (C) Copyright 2008-2016 PurplePixie Systems

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

function fnmysql_error($error)
{
	global $NATS;
	if (!isset($NATS)) return false;
	$NATS->Event("MySQL Test: " . $error, 2, "Test", "MySQL");
}



// Data Test
function mysql_test_data($host, $user, $pass, $database = "", $timeout = 0, $query = "", $column = 0, $debug = false)
{
	global $NATS;
	$sql = mysqli_init();
	if ($timeout > 0) $timeout = $timeout; // use specific for test if set
	else {
		// otherwise use system if available
		if (isset($NATS)) $timeout = $NATS->Cfg->Get("test.mysql.timeout", 0);
		if ($timeout <= 0) $timeout = 0; // unset specifically or in environment
	}
	// this will return a 0 at any stage if connect etc is ok but no rows are returned
	// negative if something actually fails
	if ($timeout > 0) {
		$oldtimeout = ini_get("mysql.timeout");
		ini_set("mysql.timeout", $timeout);
		mysqli_options($sql, MYSQLI_OPT_CONNECT_TIMEOUT, $timeout);
	}

	if ($debug) echo "mysql://" . $user . ":" . $pass . "@" . $host . "/" . $database . "\n";

	if (!is_numeric($column)) $column = 0;
	if ($column < 0) $column = 0;

	if ($debug) echo "Connecting\n";

	$connect = @mysqli_real_connect($sql, $host, $user, $pass);

	if ((!$sql) || ($database == "") || !$connect) {
		if ($timeout > 0) {
			ini_set("mysql.timeout", $oldtimeout);
			mysqli_options($sql, MYSQLI_OPT_CONNECT_TIMEOUT, $oldtimeout);
		}
		if (!$sql) {
			if ($debug) echo "Connect Error: Failed to Connect\n";
			fnmysql_error("Failed to Connect");
			return -1; // total connect failed
		}
		// otherwise is no database so close and return -1 to indicate failure (for the data requires a DB+qry etc)
		if ($debug) echo "Connect Error: Failed to Connect with valid SQL token or null database\n";
		@mysqli_close($sql);
		return -1;
	}

	if ($debug) echo "Selecting DB\n";

	@mysqli_select_db($sql, $database);

	if ($sql === false || @mysqli_errno($sql) != 0) // failed to select DB
	{
		if ($timeout > 0) {
			ini_set("mysql.timeout", $oldtimeout);
			mysqli_options($sql, MYSQLI_OPT_CONNECT_TIMEOUT, $oldtimeout);
		}
		fnmysql_error(mysqli_error($sql));
		@mysqli_close($sql);
		return -2;	// select database failed
	}

	if ($query == "") { // no query to perform
		if ($timeout > 0) {
			ini_set("mysql.timeout", $oldtimeout);
			mysqli_options($sql, MYSQLI_OPT_CONNECT_TIMEOUT, $oldtimeout); 
		}
		@mysqli_close($sql);
		return -1; // all ok but no query/rows
	}

	$r = @mysqli_query($sql, $query);
	if (mysqli_errno($sql) == 0) // successful query
	{
		if (is_bool($r)) // didn't return any data
		{
			$return = -4; // so for this purpose (data) the query failed
		} else {
			if ($row = mysqli_fetch_array($r)) { // got data ok
				if (isset($row[$column])) $return = $row[$column];
				else $return = $row[0];
			} else $return = -5; // query seemed to succeed but no data at all here
			@mysqli_free_result($r, $sql); // free if a result
		}
	} else {
		fnmysql_error(mysqli_error($sql));
		$return = -3; // query failed
	}

	@mysqli_close($sql);
	if ($timeout > 0) {
		ini_set("mysql.timeout", $oldtimeout);
		mysqli_options($sql, MYSQLI_OPT_CONNECT_TIMEOUT, $oldtimeout);
	}
	return $return;
}



// Row Test
function mysql_test_rows($host, $user, $pass, $database = "", $timeout = 0, $query = "", $debug = false)
{
	global $NATS;
	if ($timeout > 0) $timeout = $timeout; // use specific for test if set
	else {
		// otherwise use system if available
		if (isset($NATS)) $timeout = $NATS->Cfg->Get("test.mysql.timeout", 0);
		if ($timeout <= 0) $timeout = 0; // unset specifically or in environment
	}

	$sql = mysqli_init();

	// this will return a 0 at any stage if connect etc is ok but no rows are returned
	// negative if something actually fails
	if ($timeout > 0) {
		$oldtimeout = ini_get("mysql.timeout");
		ini_set("mysql.timeout", $timeout);
		mysqli_options($sql, MYSQLI_OPT_CONNECT_TIMEOUT, $timeout);
	}

	if ($debug) echo "mysqli://" . $user . ":" . $pass . "@" . $host . "/" . $database . "\n";

	$connect = @mysqli_real_connect($sql, $host, $user, $pass);

	if ((!$sql) || ($database == "") || !$connect) {
		if ($timeout > 0) {
			ini_set("mysql.timeout", $oldtimeout);
			mysqli_options($sql, MYSQLI_OPT_CONNECT_TIMEOUT, $oldtimeout);
		}
		if (!$sql || !$connect) {
			if ($debug) echo "Connect Error: Failed to Connect\n";
			fnmysql_error("Failed to Connect");
			return -1; // total connect failed
		}
		// otherwise is no database so close and return 0
		@mysqli_close($sql);
		return 0;
	}

	@mysqli_select_db($sql, $database);

	if ($sql === false || mysqli_errno($sql) != 0) // failed to select DB
	{
		if ($timeout > 0) {
			ini_set("mysql.timeout", $oldtimeout);
			mysqli_options($sql, MYSQLI_OPT_CONNECT_TIMEOUT, $oldtimeout);
		}
		fnmysql_error(mysqli_error($sql));
		@mysqli_close($sql);
		return -2;	// select database failed
	}

	if ($query == "") { // no query to perform
		if ($timeout > 0) {
			ini_set("mysql.timeout", $oldtimeout);
			mysqli_options($sql, MYSQLI_OPT_CONNECT_TIMEOUT, $oldtimeout);
		}
		@mysqli_close($sql);
		return 0; // all ok but no query/rows
	}

	$r = @mysqli_query($sql, $query);
	if (mysqli_errno($sql) == 0) // successful query
	{
		if (is_bool($r)) // didn't return any daya
		{
			$return = mysqli_affected_rows($sql);
		} else {
			$return = mysqli_num_rows($r);
			@mysqli_free_result($r); // free if a result
		}
	} else {
		fnmysql_error(mysqli_error($sql));
		$return = -3; // query failed
	}

	if ($timeout > 0) {
		ini_set("mysql.timeout", $oldtimeout);
		mysqli_options($sql, MYSQLI_OPT_CONNECT_TIMEOUT, $oldtimeout);
	}
	@mysqli_close($sql);
	return $return;
}

function mysql_test_time($host, $user, $pass, $database = "", $timeout = 0, $query = "", $debug = false)
{
	$timer = new TFNTimer();
	$timer->Start();
	$val = mysql_test_rows($host, $user, $pass, $database, $timeout, $query, $debug);
	$time = $timer->Stop();

	if ($val < 0) return $val; // connect/select/query failed

	// if $val is 0 then nothing was returned - maybe check the query here? Complicates the idea
	// though for the user so left. Will have to do two tests for both time and rows 0 as fails

	$time = round($time, 4);
	if ($time == 0) return "0.0001";
	return $time;
}


if (isset($NATS)) {
	class FreeNATS_MySQL_Test extends FreeNATS_Local_Test
	{
		function DoTest($testname, $param, $hostname = "", $timeout = -1, $params = false)
		{ // 0: host, 1: user, 2: pass, 3: database, 4: query

			if ($testname == "mysql") {
				$ip = ip_lookup($param);
				if ($ip == "0") return -1; // cache only as 127.0.0.1 is not the same connection as localhost for MySQL auth!

				return mysql_test_time($param, $params[1], $params[2], $params[3], $timeout, $params[4]);
			} else if ($testname == "mysqlrows") {
				$ip = ip_lookup($param);
				if ($ip == "0") return -1; // cache only - see above
				return mysql_test_rows($param, $params[1], $params[2], $params[3], $timeout, $params[4]);
			} else if ($testname == "mysqldata") {
				$ip = ip_lookup($param);
				if ($ip == "0") return -1;
				return mysql_test_data($param, $params[1], $params[2], $params[3], $timeout, $params[4], $params[5]);
			} else return -1;
		}
		function Evaluate($result)
		{ // same for all types
			if ($result < 0) return 2; // failed
			return 0; // passed
		}

		function ProtectOutput(&$test)
		{
			$test['testparam2'] = "";
		}

		function DisplayForm(&$row)
		{
			$optional = true;
			if ($row['testtype'] == "mysqldata") $optional = false;
			echo "<table border=0>";
			echo "<tr><td align=left>";
			echo "Hostname :";
			echo "</td><td align=left>";
			echo "<input type=text name=testparam size=30 maxlength=128 value=\"" . $row['testparam'] . "\">";
			echo "</td></tr>";
			echo "<tr><td align=left>";
			echo "Username :";
			echo "</td><td align=left>";
			echo "<input type=text name=testparam1 size=30 maxlength=128 value=\"" . $row['testparam1'] . "\">";
			echo "</td></tr>";
			echo "<tr><td align=left>";
			echo "Password :";
			echo "</td><td align=left>";
			echo "<input type=text name=testparam2 size=30 maxlength=128 value=\"\">";
			echo "<input type=hidden name=keepparam2 value=1>";
			echo "</td></tr>";
			echo "<tr><td colspan=2><i>Leave blank to not change or <input type=checkbox name=clearparam2 value=1> click to clear</i></td></tr>";
			echo "<tr><td align=left>";
			echo "Database :";
			echo "</td><td align=left>";
			echo "<input type=text name=testparam3 size=30 maxlength=128 value=\"" . $row['testparam3'] . "\">";
			echo "</td></tr>";
			if ($optional) echo "<tr><td colspan=2><i>Optional - leave blank to not bother with select_db</td></tr>";
			echo "<tr><td align=left>";
			echo "Query :";
			echo "</td><td align=left>";
			echo "<input type=text name=testparam4 size=30 maxlength=128 value=\"" . $row['testparam4'] . "\">";
			echo "</td></tr>";
			if ($optional) echo "<tr><td colspan=2><i>Optional - leave blank to not bother with a query</td></tr>";
			if ($row['testtype'] == "mysqldata") {
				echo "<tr><td align=left>";
				echo "Column :";
				echo "</td><td align=left>";
				echo "<input type=text name=testparam5 size=2 maxlength=8 value=\"" . $row['testparam5'] . "\">";
				echo "</td></tr>";
				echo "<tr><td colspan=2><i>Which field (0 is first and the default) of the first record to use</i></td></tr>";
			}
			echo "</table>";
		}
	}
	$params = array();
	$NATS->Tests->Register("mysql", "FreeNATS_MySQL_Test", $params, "MySQL Connect", 3, "FreeNATS MySQL Tester");
	$NATS->Tests->SetUnits("mysql", "Seconds", "s");
	$NATS->Tests->Register("mysqlrows", "FreeNATS_MySQL_Test", $params, "MySQL Rows", 3, "FreeNATS MySQL Tester");
	$NATS->Tests->SetUnits("mysqlrows", "Rows", "rows");
	$NATS->Tests->Register("mysqldata", "FreeNATS_MySQL_Test", $params, "MySQL Data", 3, "FreeNATS MySQL Tester");
}
