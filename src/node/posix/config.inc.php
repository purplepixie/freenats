<?php // config.inc.php
// FreeNATS Push/Pull XML Node for Posix Environments Configuration
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

// Configuration Section

$nodeCfg['nodeid']			=	"";	// nodeid for FreeNATS (required)

$nodeCfg['nodekey']			=	""; // alphanumeric node key

// Pull method - polled from FreeNATS server

$nodeCfg['allowpull']		=	true; // allow pull mode
$nodeCfg['restrict_pull_ip']=	""; // limit IPs allowed to pull data

// Push method - polled locally and HTTP POSTd to FreeNATS server

$nodeCfg['allowpush']	=		true; // allow push mode
$nodeCfg['push_target']	=		""; // Full http://YOUR_FREENATS/nodeside.push.php


$nodeCfg['tmp_dir']		=		"/tmp/fnn."; // tmp dir (and optional file prefix)
								// for temp delta-calculation files e.g. network speed


// Individual Test Sections Enabled/Disabled

$nodeCfg['uptime']			=	true;
$nodeCfg['disk']			=	true;
$nodeCfg['memory']			=	true;
$nodeCfg['net']				=	true;
$nodeCfg['systime']			=	true;
$nodeCfg['process']			=	true;
?>