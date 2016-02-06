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

if (!isset($BaseDir)) $BaseDir="./";

require($BaseDir."config.inc.php");

// Stuff...
require($BaseDir."help.inc.php");
require($BaseDir."node.xml.inc.php");


// Modules
require($BaseDir."nats.db.inc.php");
require($BaseDir."nats.cfg.inc.php");
require($BaseDir."nats.tests.inc.php");
require($BaseDir."nats.lang.inc.php");

require($BaseDir."rss.inc.php");


require($BaseDir."freenats.inc.php");
$NATS=new TFreeNATS();

// Timer
require($BaseDir."timer.inc.php");

// Tests
require($BaseDir."eval.inc.php");
require($BaseDir."tests.inc.php");

// Scheduling Support
require($BaseDir."schedule.inc.php");


$NATS->Start();

// Session Management
require($BaseDir."session.inc.php");
$NATS_Session=new TNATS_Session();





// Screen and Stuff
require($BaseDir."screen.inc.php");
require($BaseDir."testtext.inc.php");
require($BaseDir."view.inc.php");
?>