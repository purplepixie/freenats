<?php
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

/*

Build Configuration Options

*/

$_FREENATS_SRC = "./src/";
$_FREENATS_BASE = $_FREENATS_SRC."server/base/";
$_FREENATS_NATS = $_FREENATS_BASE."nats.php";
$_FREENATS_RELEASE = "./release/";

// If FREENATS_DB_CONFIG is FALSE then the FreeNATS config.inc.php
// will be used (standard behaviour). Override here if needed.
$_FREENATS_DB_CONFIG = false;
$_FREENATS_DB_SERVER = "";
$_FREENATS_DB_DATABASE = "";
$_FREENATS_DB_USERNAME = "";
$_FREENATS_DB_PASSWORD = "";

// For override of official release URL (normally uploaded to Purplepixie.org)
$_FREENATS_UPLOAD_URL = "";

?>