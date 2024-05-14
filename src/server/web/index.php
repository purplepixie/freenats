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
ob_start();
require("include.php");
$NATS->Start();
$session = $NATS_Session->Check($NATS->DB);
if ($session) {
	header("Location: main.php");
	exit();
}

if (($NATS->Cfg->Get("site.auth", "") == "http") &&
	((!isset($_REQUEST['auth'])) || ($_REQUEST['auth'] != "basic"))
) {
	header("Location: login.php");
	exit();
}


Screen_Header($NATS->Lang->Item("welcome"), 3);
ob_end_flush();
?>
<br>
<center>
	<?php
	//if (isset($_REQUEST['login_msg'])) echo "<b style=\"color: red; font-size: 14pt;\">".$_REQUEST['login_msg']."</b><br><br>";
	$mesg = array(
		0 => "msg.logout",
		1 => "msg.session",
		2 => "msg.loginfailed"
	);
	if (isset($_REQUEST['msg'])) $msg = $_REQUEST['msg'];
	else if (isset($_REQUEST['login_msg'])) // legacy support
	{
		if ($_REQUEST['login_msg'] == "Invalid Or Expired Session") $msg = 1;
	}

	if (isset($msg) && isset($mesg[$msg])) {
		echo "<b style=\"color: red; font-size: 14pt;\">" . $NATS->Lang->Item($mesg[$msg]) . "</b><br><br>";
	} else echo "<b style=\"font-size: 14pt;\">" . $NATS->Lang->Item("welcome") . "</b><br><br>";

	$t = "<b class=\"subtitle\">Login...</b>";
	Start_Round($NATS->Lang->Item("auth"), 300);
	?><center><br>
		<table border=0 width=200>
			<form action=login.php method=post>
				<?php
				if (isset($_REQUEST['url'])) echo "<input type=hidden name=\"url\" value=\"" . $_REQUEST['url'] . "\">";
				?>
				<tr>
					<td align=right>
						<b><?php echo $NATS->Lang->Item("username"); ?>: </b>
					</td>
					<td><input type=text name=naun size=20 maxlength=32 style="width: 160px;"></td>
				</tr>
				<tr>
					<td align=right><b><?php echo $NATS->Lang->Item("password"); ?>: </b></td>
					<td><input type=password name=napw size=21 style="width: 160px;" maxlenth=64></td>
				</tr>
				<tr>
					<td align=right><b><?php echo $NATS->Lang->Item("language"); ?>: </b></td>
					<td><select name=nala style="width: 160px;">
							<option value="">System Default</option>
							<?php
							$langs = $NATS->Lang->GetLanguages();
							foreach ($langs as $code => $lang) {
								echo "<option value=\"" . $code . "\">" . $lang . " (" . $code . ")</option>\n";
							}
							?>
						</select></td>
				</tr>
		</table><br>
		<?php
		echo "<input type=submit value=\"" . $NATS->Lang->Item("login") . "\" style=\"font-size: 11pt;\">\n";
		?>
		<!-- <br><input type=checkbox name=gotomonitor value=1> Go straight to live monitor</input> -->
		</form>
	</center><br>

	<?php
	End_Round();
	?>
	<br><br>
</center>
<?php
Screen_Footer();
?>