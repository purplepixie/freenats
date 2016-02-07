<?php
// Usage logrel.php version file [URL]
if ($argc!=3 && $argc!=4)
 {
 echo "Usage: php release.php version filename [remote release URL]\n";
 exit();
 }

if (!function_exists("curl_init"))
{
	echo "Upload requires PHP-CURL - aborting\n";
	exit();
}

 $remoteURL = "http://www.purplepixie.org/freenats/upload-release.php";
 // Testing URL for local shadow copy
 //$remoteURL = "http://127.0.0.1:8888/Code/purplepixie/freenats-web/upload-release.php";

 if ($argc==4)
 	$remoteURL = $_SERVER['argv'][3];
 
 
$v=$argv[1];
echo "Version: ".$v."\n";
$f=$argv[2];
echo "File   : ".$f."\n";

if (!file_exists($f))
{
	echo "File does not exist - aborting\n";
	exit();
}

echo "Username: ";
$username=trim(fgets(STDIN));
echo "Password: ";
$password=trim(fgets(STDIN));

$fp=fopen($remoteURL."?mode=check&version=".urlencode($v),"r");
if ($fp <= 0)
{
	echo "Failed to open URL: ".$remoteURL."\n";
	exit();
}
$resp=fgets($fp, 1024);
fclose($fp);

if ($resp == "0")
{
	echo "Version ".$v." does not already exist in database - continuing\n";
}
else if ($resp == "1")
{
	echo "Version ".$v." DOES exist in database - aborting\n";
	exit();
}
else
{
	echo "Other response received from server: ".$resp."\n";
	echo "Aborting\n";
	exit();
}


echo "Type: (R)elease or (d)evelopment: ";
$type=trim(fgets(STDIN));

$rel=true;
if ($type=="d") $rel=false;

echo "Public: (Y)es or (n)o: ";
$pub=trim(fgets(STDIN));
$public=true;
if ($pub=="n") $public=false;

$current=false;
$news=false;

if ($public)
{
	echo "Current: (Y)es or (n)o: ";
	$pub=trim(fgets(STDIN));
	$current=true;
	if ($pub=="n") $current=false;
	
	echo "News Item: (Y)es or (n)o: ";
	$pub=trim(fgets(STDIN));
	$news=true;
	if ($pub=="n") $news=false;
}

$rnotes="";
$changelog="";

echo "Release Notes (. on a single line to quit):\n";

$ins="";
while ($ins!=".")
{
	$ins=trim(fgets(STDIN));
	if ($ins!=".") $rnotes.=$ins."\n";
}
	
echo "Change Log (. on a single line to quit):\n";

$ins="";
while ($ins!=".")
{
	$ins=trim(fgets(STDIN));
	if ($ins!=".") $changelog.=$ins."\n";
}

echo "Version: ".$v."\n";
echo "Type   : ";
if ($rel) echo "Release";
else echo "Development";
echo "\n";
echo "Public : ";
if ($public) echo "Yes";
else echo "No";
echo "\n";
echo "News   : ";
if ($news) echo "Yes";
else echo "No";
echo "\n";
echo "Current: ";
if ($current) echo "Yes";
else echo "No";
echo "\n";
echo "Release Notes:\n";
echo $rnotes;
echo "\nChange Log:\n";
echo $changelog;

echo "\n\n";

echo "Proceed? (y/N): ";
$proc=trim(fgets(STDIN));
if ($proc != "y" && $proc != "Y")
{
	echo "Aborted\n";
	exit();
}

// version
// release (1 = release, 0 = development)
// public (1/0)
// current (1/0)
// news (1/0)
// releasenotes
// changelog

$md5=md5_file($f);
$filepath = realpath($f);

$post_data = array(
	"mode" => "release",
	"version" => $v,
	"username" => $username,
	"password" => $password,
	"release" => ($rel ? 1 : 0),
	"public" => ($public ? 1 : 0),
	"news" => ($news ? 1 : 0),
	"current" => ($current ? 1 : 0),
	"releasenotes" => $rnotes,
	"changelog" => $changelog,
	"md5" => $md5,
	"file" => new CurlFile($filepath),'application/x-gzip');

$ch=curl_init();
//$headers = array("Content-Type:multipart/form-data", "Expect:");
$headers = array("Content-Type:multipart/form-data");
curl_setopt($ch, CURLOPT_URL, $remoteURL);
//curl_setopt($ch, CURLOPT_URL, "http://www.purplepixie.org/freenats/");
curl_setopt($ch, CURLOPT_HEADER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//curl_setopt($ch, CURLOPT_POSTFIELDS, "file=@".$filepath."&".http_build_query($post_data,"","&"));
//curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data,"","&"));
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//curl_setopt($ch, CURLOPT_USERAGENT, "User-Agent: Mozilla/5.0 (Windows NT 6.3; WOW64; rv:31.0) Gecko/20100101 Firefox/31.0");
curl_setopt($ch, CURLOPT_VERBOSE, 1);

$response=curl_exec($ch);
curl_close($ch);

echo $response;
echo "\n";



?>