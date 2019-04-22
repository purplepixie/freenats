<?php
/* -------------------------------------------------------------
This file is part of FreeNATS

FreeNATS is (C) Copyright 2008-2019 PurplePixie Systems

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

// API Error
function make_api_error($httpcode, $errorcode, $message)
{
    $a = array(
		"type" => "error",
        "error" => true,
		"httpcode" => $httpcode,
        "errorcode" => $errorcode,
        "message" => $message
    );
    return $a;
}

function throw_api_error($httpcode, $errorcode, $message)
{
	$err = make_api_error($httpcode,$errorcode,$message);
	if ($httpcode != 200)
		http_response_code($httpcode);
	echo json_encode($err);
	exit();
}

function api_response($response)
{
	echo json_encode($response);
	exit();
}

// CORS allow
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS')
{
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET, DELETE, PUT, PATCH, OPTIONS');
    header('Access-Control-Allow-Headers: token, Content-Type');
    header('Access-Control-Max-Age: 1728000');
    header('Content-Length: 0');
    header('Content-Type: text/plain');
    exit();
}


ob_start();
require("include.php");
$NATS->Start();
$session=true;
if (!$NATS_Session->Check($NATS->DB,false,false))
{
	$session=false;
}

// General response holder for consistency
$response = array(
	"type" => "response",
	"error" => false,
	"httpcode" => 200
);

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$abs=GetAbsolute();
$headers = getallheaders();

$uri = $_SERVER['REQUEST_URI'];
$route = substr($uri,strpos($uri,"jsonapi.php")+strlen("jsonapi.php"));
if ($route[0] == "/")
	$route=substr($route,1);
if (strpos($route,"?")!==false)
	$route=substr($route,0,strpos($route,"?"));
$routeparts = explode("/",$route);
$mainroute = $routeparts[0];


// Unauthenticated route - login
if ($route == "login")
{
	$username = isset($_REQUEST['username']) ? $_REQUEST['username'] : (isset($_REQUEST['naun']) ? $_REQUEST['naun'] : "");
	$password = isset($_REQUEST['password']) ? $_REQUEST['password'] : (isset($_REQUEST['napw']) ? $_REQUEST['napw'] : "");



	$res = $NATS_Session->Create($NATS->DB,$username,$password);
	if ($res === false) // login failed
		throw_api_error(403,"LOGINFAILED","Failed to login with username and password provided");
	else
	{
		$response['skey']=$NATS_Session->sessionkey;
		$response['sid']=$NATS_Session->sessionid;
		api_response($response);
	}
}
// api.public - is available without session auth
// api.key - usage key used if public and no session (if set)

if ($NATS->Cfg->Get("api.public",0)!=1) // NOT public
{
	if (!$session)
	{
		throw_api_error(403,"UNAUTHORISED","No public access to the API");
	}
}
else if (!$session) // IS PUBLIC and not logged in
{
	$key=$NATS->Cfg->Get("api.key","");
	if ($key!="") // require a key
	{
		$userkey = isset($_REQUEST['apikey']) ? $_REQUEST['apikey'] : isset($headers["X-Auth-Token"]) ? $_REQUEST['X-Auth-Token'] : false;
		if ( ($userkey === false) || ($userkey != $key) )
		{
			// No key or doesn't match
			throw_api_error(403,"UNAUTHORISED","Key mismatch for public API access");
		}
	}
}

// ROUTES
if ($mainroute == "alerts")
{
	$alerts=$NATS->GetAlerts();
	$response['alertcount']=count($alerts);
	$response['alerts']=$alerts;
	api_response($response);
}
elseif ($mainroute == "nodes")
{
	$nodes=$NATS->GetNodes();
	$response['nodecount']=count($nodes);
	$response['nodes']=$nodes;
	api_response($response);
}
elseif ($mainroute == "node")
{
	$nodeid = isset($routeparts[1]) ? $routeparts[1] : false;
	$node = $NATS->GetNode($nodeid);
	if ($node === false)
		throw_api_error(404,"NONODE","Node not found");
	$tests = $NATS->GetNodeTests($nodeid);
	$response['nodeid']=$node['nodeid'];
	$response['node']=$node;
	$response['testcount']=count($tests);

	$response['tests']=array();
	foreach($tests as $testid)
	{
		$test=$NATS->GetTest($testid,true);
		$response['tests'][]=$test;
	}

	api_response($response);
}
elseif ($mainroute == "test")
{
	$testid = isset($routeparts[1]) ? $routeparts[1] : false;
	$test = $NATS->GetTest($testid, true);
	if ($test === false)
		throw_api_error(404,"NOTEST","Test not found");
	$response['testid']=$testid;
	$response['nodeid']=$test['nodeid'];
	$response['test']=$test;

	api_response($response);
}
elseif ($mainroute == "version")
{
	$v=$NATS->Version;
	$r=$NATS->Release;
	$vp=explode(".",$v);
	$response['version']=$v;
	$response['release']=$r;
	$response['compound']=$v.$r;
	$response['major']=$vp[0];
	$response['minor']=$vp[1];
	$response['patch']=$vp[2];
	api_response($response);
}





// We get here so route not found
throw_api_error(404,"NOROUTE","Route not found or not provided");
