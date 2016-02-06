<?php
ob_start();
require("include.php");
$NATS->Start();
if (!$NATS_Session->Check($NATS->DB))
	{
	header("Location: ./?login_msg=Invalid+Or+Expired+Session");
	exit();
	}


if (($NATS->Cfg->Get("site.auth")=="http") && isset($_SERVER['PHP_AUTH_USER']))	
{
	Screen_Header($NATS->Lang->Item("httpa.logout"),1);
	echo "<H1>".$NATS->Lang->Item("httpa.logout")."</H1>\n";
	echo $NATS->Lang->Item("httpa.logout.desc")."<BR /><BR />\n";
	echo "<A HREF=\"main.php\">".$NATS->Lang->Item("click.continue")."</A><BR /><BR />\n";
	Screen_Footer();
	exit();
}

	
$NATS_Session->Destroy($NATS->DB);
setcookie("fn_lang","");


header("Location: ./?msg=0");
exit();
?>
