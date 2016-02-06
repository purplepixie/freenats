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
along with Foobar.  If not, see www.gnu.org/licenses

For more information see www.purplepixie.org/freenats
-------------------------------------------------------------- */

require("include.php");
//$NATS->Start();
//$NATS_Session->Check($NATS->DB);
// stuff it... hackers can see our help!! woo-hoo!!

echo "<html><head><title>FreeNATS Help</title>\n";
echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"css/main.css\">\n";
echo "</head><body>";


echo hdisp($_REQUEST['id']);

echo "<br><br>";
echo "For more information see <a href=http://www.purplepixie.org/freenats/ target=top>www.purplepixie.org/freenats</a><br><br>";

echo "<a href=\"javascript:window.close()\">Close Window</a>";

echo "</body></html>";
?>
