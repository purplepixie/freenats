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
$msg="";
ob_start();
if (isset($_REQUEST['dirindex'])) $dirindex=$_REQUEST['dirindex'];
else $dirindex=0;

if (isset($_REQUEST['filename'])) $filename=$_REQUEST['filename'];
else $filename="";

if ($filename!="")
	{
	if ($filename[0]=="/") $filename=substr($filename,1);
	if (strpos($filename,"..")!==false) $filename="";
	}

require("include.php");
$NATS->Start();
if (!$NATS_Session->Check($NATS->DB))
	{
	header("Location: ./?login_msg=Invalid+Or+Expired+Session");
	exit();
	}
if ($NATS_Session->userlevel<9) UL_Error("Filemanager Interface");

$dirs=array();
$dircount=0;
function add_dir($name,$path)
{
global $dirs,$dircount;
$dirs[$dircount]['name']=$name;
$dirs[$dircount]['path']=$path;
$dircount++;
return ($dircount-1);
}

add_dir("Site Tests",$BaseDir."site/tests/");
add_dir("Site Events",$BaseDir."site/events/");

// Actions Here
if (isset($_REQUEST['action']))
	{
	switch ($_REQUEST['action'])
		{
		case "save":
		$fp=@fopen($dirs[$dirindex]['path'].$filename,"w");
		if ($fp<=0)
			{
			$msg="Failed to Open File to Save";
			}
		else
			{
			$size=strlen($_REQUEST['content']);
			fputs($fp,$_REQUEST['content'],$size);
			fclose($fp);
			$msg="File Saved";
			}
		break;
		
		case "delete":
		$fn=$dirs[$dirindex]['path'].$filename;
		if (!isset($_REQUEST['confirmed']))
			{
			$cl="filemanager.php?dirindex=".$dirindex."&filename=".$filename."&action=delete&confirmed=1";
			$loc="confirm.php?action=Delete+file+".$filename."&back=".urlencode($cl);
			header("Location: ".$loc);
			exit();
			}
		$res=@unlink($fn);
		if ($res) $msg="Deleted File ".$filename;
		else $msg="Failed to Delete ".$fn;
		break;
		
		case "download":
		
		$fn=$dirs[$dirindex]['path'].$filename;
		if (file_exists($fn))
			{
			header("Content-type: application/octet-stream");
			header("Content-Length: ".filesize($fn));
			header("Content-Disposition: attachment; filename=".$filename);
			header("Content-Transfer-Encoding: binary");
			$fp=@fopen($fn,"rb");
			if ($fp)
				{
				fpassthru($fp);
				fclose($fp);
				}
			exit();
			}
		$msg="File Download Failed";
		break;
		
		case "upload":
		$uploadfn = $dirs[$dirindex]['path'] . basename($_FILES['uploadfile']['name']);

		if (move_uploaded_file($_FILES['uploadfile']['tmp_name'], $uploadfn)) 
			{
			$msg="File Uploaded Ok";
			}
		else $msg="File Upload Failed";

		break;
		
		}
	}

Screen_Header("File Manager",1,1,"","main","admin");
if ($msg!="") echo "<b>".$msg."</b><br><br>";

echo "<br><b class=\"subtitle\"><a href=admin.php>System Settings</a> &gt; File Manager</b><br><br>";

echo "<form action=filemanager.php method=post>";
echo "<b>Change Directory: <select name=dirindex>";
for($a=0;$a<$dircount;$a++)
 {
 echo "<option value=".$a.">".$dirs[$a]['name']." (".$dirs[$a]['path'].")</option>";
 }
echo "</select> <input type=submit value=Go> </form>";
echo "<br><br>";

echo "<b class=\"subtitle\">".$dirs[$dirindex]['name']." Directory: ".$dirs[$dirindex]['path']."</b><br><br>";

if ($handle=opendir($dirs[$dirindex]['path']))
	{
	echo "<table class=\"nicetable\">";
    while (false !== ($file = readdir($handle)))
    	{
	    if ( ($file!=".l") && ($file!=".l.") )
	    	{
	        echo "<tr><td>";
	        if (is_dir($dirs[$dirindex]['path'].$file))
	        	{
	        	echo $file;
	        	$isfile=false;
        		}
	        else
	        	{
		        $isfile=true;
		        echo "<a href=filemanager.php?action=download&dirindex=".$dirindex."&filename=".$file.">";
	        	echo $file;
	        	echo "</a>";
        		}
        	echo "</td>";
        	
        	echo "<td>";
        	if ($isfile)
        		{
	        	echo "<a href=filemanager.php?action=edit&dirindex=".$dirindex."&filename=".$file.">";
	        	echo "<img src=images/options/reply.png border=0></a> ";
	        	echo "<a href=filemanager.php?action=delete&dirindex=".$dirindex."&filename=".$file.">";
	        	echo "<img src=images/options/action_delete.png border=0></a>";
        		}
        	else echo "&nbsp;";
        	echo "</td>";
        	
        	echo "<td>";
        	if ($isfile)
        		{
	        	echo filesize($dirs[$dirindex]['path'].$file)." bytes";
        		}
        	else echo "&nbsp;";
        	echo "</td>";
	        	
	        
	        echo "</tr>";
        	}
    	}

	echo "</table>";
    closedir($handle);
	}

echo "<form enctype=\"multipart/form-data\" method=\"POST\" action=\"filemanager.php\">";
echo "<input type=hidden name=action value=upload>";
echo "<input type=hidden name=dirindex value=".$dirindex.">";
echo "<b>Upload File: </b><input type=file name=uploadfile> <input type=submit value=Upload> </form><br><br>";
	
echo "<form action=filemanager.php method=post>";
echo "<input type=hidden name=dirindex value=".$dirindex.">";
echo "<b>Create File Named: </b><input type=text name=filename size=30> <input type=submit value=Create>";
echo "<input type=hidden name=action value=edit></form>";
	
echo "<br><br>";

if ( isset($_REQUEST['action']) && ($_REQUEST['action']=="edit") )
	{
	$text=@file_get_contents($dirs[$dirindex]['path'].$filename);
	echo "<form action=filemanager.php method=post>";
	echo "<input type=hidden name=action value=save>";
	echo "<input type=hidden name=dirindex value=".$dirindex.">";
	echo "<input type=hidden name=filename value=".$filename.">";
	echo "<b class=\"subtitle\">Editing ".$dirs[$dirindex]['path'].$filename."</b><br><br>";
	echo "<textarea name=content cols=80 rows=30>";
	echo htmlspecialchars($text);
	echo "</textarea><br>";
	echo "<input type=submit value=\"Save File Content\"> <a href=filemanager.php?dirindex=".$dirindex.">Abandon Changes</a>";
	echo "</form><br><br>";
	}
	
Screen_Footer();
?>
