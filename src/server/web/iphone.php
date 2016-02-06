<?php
/* -------------------------------------------------------------
This file is part of FreeNATS

Portions of the iPhone interface code are derived from the Apple
development tools. Where applicable - their notices and licence
are kept intact and apply.

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

function ipScreenHeader($title,$back="",$maintitle="FreeNATS")
{
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>

  <head>
    <title>FreeNATS iPhone</title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0">
	<!-- The browser.css file contain all information required to style the pages -->
    <!-- <link rel="stylesheet" href="fnipbrowser.css"> -->
    <link rel="stylesheet" href=css/iphone.css>
  </head>

  <body>
    <!-- this is the container for the whole content -->
    <div id="browser">
      <!--
        this is the container for the header, the area at the top of the screen with the title and back button:
        we keep two of buttons and titles so that we can transition a pair in and another pair out in transitions.
      -->
      <div id="header">
       <?php
       if ($back!="") echo "<div id=\"first_button\" class=\"button\" onclick=\"javascipt:backbuttonclick('".$back."')\">Back</div>\n";
       ?>
        <div id="first_title" class="title"><?php echo $title; ?></div>
        
      </div>
      
      <script src="js/iphone.js" type="text/javascript"></script>
      
<?php
}

function ipScreenFooter()
{
?>

<script type="text/javascript">
function hide_address_bar () {
  window.scrollTo(0, 1);
  setTimeout(function () {
    window.scrollTo(0, 0);
  }, 0);
};
        
setTimeout(hide_address_bar, 200);
</script>

    
  </body>

</html>
<?php
}

$lic=0;

function li($text,$link="")
{
global $lic;
echo "<li id=\"pageli".$lic."\"";
if ($link!="")
	{
	echo " class=\"group\" onclick=\"javascript:liclick('pageli".$lic."','".$link."')\"";
	}
echo ">".$text."</li>\n";
$lic++;
}

function lititled($text,$under)
	{
	echo "<li>".$text."<br>";
	echo "<i class=\"subtext\">".$under."</i></li>";
	}

// Actual FreeNATS Interface...

require("include.php");
$NATS->Start();
if (!$NATS_Session->Check($NATS->DB))
	{
	$mode="login";
	}
else if (isset($_REQUEST['mode'])) $mode=$_REQUEST['mode'];
else $mode="main";

switch($mode)
	{
	case "main":
	ipScreenHeader("FreeNATS");
    echo "<ul>\n";
    
    $alerts=$NATS->GetAlerts();
    if ($alerts===false) $alerts=array();
    
    if (count($alerts)>0)
    	{
	    $alt="<b class=\"al2\">Alerts</b> (".count($alerts).")";
	    $alt.="<br><i class=\"subtext\">";
	    $first=true;
	    foreach($alerts as $alert)
	    	{
		    if ($first) $first=false;
		    else $alt.=", ";
		    $alt.=$alert['nodeid'];
	    	}
	    $alt.="</i>";
    	}
    else $alt="<b class=\"al0\">No Alerts</b>";
    
    li($alt,"iphone.php?mode=alerts");
    li("Groups","iphone.php?mode=groups");
    li("Nodes","iphone.php?mode=nodes");
    li("Views","iphone.php?mode=views");
    echo "</ul>\n";
    
    //echo "<div class=\"sectitle\">Stuff</div>\n";
    echo "<ul>\n";
    //echo "<li class=\"grouptitle\">Stuff</li>\n";
    li("Standard Interface","monitor.php");
    echo "</ul>\n";
    echo "<ul>\n";
    //li("Login","iphone.php?mode=login");
    li("Logoff","logout.php");
    echo "</ul>\n";
    break;
    
    case "alerts":
    ipScreenHeader("FreeNATS Alerts","iphone.php");
    echo "<ul>\n";
    $alerts=$NATS->GetAlerts();
    if ($alerts===false) li("No Alerts");
    else
    	{
	    foreach($alerts as $alert)
	    	{
		    $link="iphone.php?mode=node&nodeid=".$alert['nodeid']."&back=".urlencode("iphone.php?mode=alerts");
		    $txt="<b class=\"al".$alert['alertlevel']."\">".$alert['nodeid']."</b>";
	    	li($txt,$link); 
    		}
    	}
    echo "</ul>";
    break;
    
    case "views":
    ipScreenHeader("Views","iphone.php");
    $q="SELECT viewid,vtitle FROM fnview";
    $r=$NATS->DB->Query($q);
    echo "<ul>\n";
    while ($row=$NATS->DB->Fetch_Array($r))
    	{
	    li($row['vtitle'],"view.php?viewid=".$row['viewid']);
    	}
    echo "</ul>\n";
    break;
    
    case "groups":
    ipScreenHeader("Groups","iphone.php");
    $q="SELECT groupname,groupid FROM fngroup ORDER BY weight ASC";
    $r=$NATS->DB->Query($q);
    echo "<ul>";
    while ($row=$NATS->DB->Fetch_Array($r))
    	{
	    $lvl=$NATS->GroupAlertLevel($row['groupid']);
	    $txt="<b class=\"al".$lvl."\">".$groupname."</b>";
	    $lnk=""; // for later
	    li($txt,$lnk);
    	}
    echo "</ul>";
    break;
    
    case "nodes":
    ipScreenHeader("Nodes","iphone.php");
    $q="SELECT nodeid,nodename,alertlevel FROM fnnode WHERE nodeenabled=1 ORDER BY alertlevel DESC,weight ASC";
    $r=$NATS->DB->Query($q);
    echo "<ul>";
    while ($row=$NATS->DB->Fetch_Array($r))
    	{
	    if ($row['nodename']=="") $nodename=$row['nodeid'];
	    else $nodename=$row['nodename'];
	    $lvl=$row['alertlevel'];
	    $txt="<b class=\"al".$lvl."\">".$nodename."</b>";
	    $lnk="iphone.php?mode=node&nodeid=".$row['nodeid'];
	    li($txt,$lnk);
    	}
    echo "</ul>";
    break;
    
    case "node":
    if (isset($_REQUEST['nodeid'])) $nodeid=$_REQUEST['nodeid'];
    else $nodeid="";
    if (isset($_REQUEST['back'])) $back=$_REQUEST['back'];
    else $back="iphone.php?mode=nodes";
    ipScreenHeader($nodeid,$back);
    
    $node=$NATS->GetNode($nodeid);
    if ($node===false) echo "<ul><li>Invalid NodeID</li></ul>";
    else
    	{
	    echo "<ul>";
	    li("Configuration");
	    //lititled($node['nodeid'],"nodeid");
	    //lititled($node['nodename'],"node name");
	    //lititled($node['hostname'],"hostname");
	    echo "<li><table border=0 style=\"font-size: 10pt; font-weight: normal;\">";
	    echo "<tr><td>Node ID: </td><td align=left>".$node['nodeid']."</td></tr>";
	    echo "<tr><td>Node Name: </td><td>".$node['nodename']."</td></tr>";
	    echo "<tr><td>Hostname: </td><td>".$node['hostname']."</td></tr>";
	    echo "</table></li>\n";
	    echo "</ul>";
	    
	    echo "<ul>\n";
	    $t="Status: ";
	    $t.="<b class=\"al".$node['alertlevel']."\">";
	    $t.=oText($node['alertlevel']);
	    $t.="</b>";
	    li($t);
	    lititled($node['lastrunago']." ago",$node['lastrundt']);
	    
	    echo "</ul>";
	    
	    echo "\n<ul>\n";
	    li("Standard Interface");
	    li("Node View","node.php?nodeid=".$node['nodeid']);
	    li("Todays Summary","summary.test.php?nodeid=".$node['nodeid']);
	    li("Node Edit","node.edit.php?nodeid=".$node['nodeid']);
	    echo "</ul>\n";
	    
	    
	    
	    
    	}
    break;
    
    case "login":
    ipScreenHeader("FreeNATS");
    echo "<form action=\"login.php\" name=\"loginform\" method=post>";
    echo "<input type=hidden name=url value=iphone.php>";
    echo "<ul>\n";
    li("Username");
    echo "<li><input type=text name=naun size=20 maxlength=128 style=\"width: 250px; height: 28px; font-size: 14pt;\"></li>\n";
    echo "</ul><ul>\n";
    li("Password");
    echo "<li><input type=password name=napw size=20 maxlength=128 style=\"width: 250px; height: 28px; font-size: 14pt;\"></li>\n";
    echo "</ul><ul>\n";
    echo "<li class=\"group\" onclick=\"javascript:document.loginform.submit()\">";
    echo "Login to FreeNATS";
    echo "</li>\n";
    echo "</ul>";
    break;
    
    default:
    ipScreenHeader("FreeNATS","iphone.php");
    echo "<ul>";
    li("Error Occured");
    echo "</ul>\n";
    echo "<script type=\"text/javascript\">\n";
    echo "setTimeout( function() { alert('Unknown Mode'); },700);\n";
    echo "</script>\n";
    break;
	}


ipScreenFooter();
$NATS->Stop();
?>  
    
