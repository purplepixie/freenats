<?php
$fp = fopen("versions.txt","r");
while(!feof($fp))
{
 $line = fgets($fp,1024);
 $parts = explode(" ",$line);
 //print_r($parts);
 //exit();
 // 4 5 6
 $size=$parts[4];
 $date=$parts[5];
 $file=$parts[6];
 $file=substr($file,9);
 $file=substr($file,0,strlen($file)-8);
 echo $file.",".$date.",".$size."\n";
}
fclose($fp);
?>
