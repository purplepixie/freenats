<?php // test.graph.php
ob_start();
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

require("include.php");
$NATS->Start();




function ty($y)
{
global $height;
return $height-$y;
}

//if (!isset($_REQUEST['testid'])) $_REQUEST['testid']="L26";

// width height suid startx finishx

if (isset($_REQUEST['width'])) $width=$_REQUEST['width'];
else $width=700;
if (isset($_REQUEST['height'])) $height=$_REQUEST['height'];
else $height=150;

// other incoming stuff
if (isset($_REQUEST['draw_spike'])) $draw_spike=$_REQUEST['draw_spike'];
else $draw_spike=1;
if (isset($_REQUEST['draw_track'])) $draw_track=$_REQUEST['draw_track'];
else $draw_track=1;
if (isset($_REQUEST['draw_under'])) $draw_under=$_REQUEST['draw_under'];
else $draw_under=1;
if (isset($_REQUEST['draw_under_pass'])) $draw_under=$_REQUEST['draw_under_pass'];
else $draw_under_pass=0;

// Transparent logic (1.14.1/2)
// options in order of presidence (URI, Config, Default):
// transparent=1 in URI: transparent, transparent=0 in URI: non-transparent,
// site.graph.transparent=1: transparent, default: non-transparent
if (isset($_REQUEST['transparent']) && $_REQUEST['transparent']==1) 
  $transparent=1;
else if (isset($_REQUEST['transparent']) && $_REQUEST['transparent']==0) 
  $transparent=0;
else if ($NATS->Cfg->Get("site.graph.transparent",0)==1)
  $transparent=1;
else 
  $transparent=0;

$draw_x_grid=true;
$draw_y_grid=true;

if (isset($_REQUEST['no_x_grid'])) $draw_x_grid=false;
if (isset($_REQUEST['no_y_grid'])) $graw_y_grid=false;
if (isset($_REQUEST['no_grid']))
	{
	$draw_x_grid=false;
	$draw_y_grid=false;
	}

// start image
$im=@imagecreate($width,$height)
	or die("Cannot create image");

// setup colours
$col=array();
$col['white']=imagecolorallocate($im,255,255,255);
$col['black']=imagecolorallocate($im,0,0,0);
$col['red']=imagecolorallocate($im,250,50,50);
$col['lightgrey']=imagecolorallocate($im,200,200,200);
$col['verylightgrey']=imagecolorallocate($im,240,240,240);
$col['blue']=imagecolorallocate($im,150,150,255);
$col['green']=imagecolorallocate($im,0,200,0);
$col['lightgreen']=imagecolorallocate($im,150,250,150);
$col['grey']=imagecolorallocate($im,150,150,150);
$col['orange']=imagecolorallocate($im,200,200,0);

if (isset($_REQUEST['bgcol'])) $c_bg=$col[$_REQUEST['bgcol']];
else $c_bg=$col['white'];

if (isset($_REQUEST['txtcol'])) $c_txt=$col[$_REQUEST['txtcol']];
else $c_txt=$col['black'];

if (isset($_REQUEST['axescol'])) $c_axes=$col[$_REQUEST['axescol']];
else $c_axes=$col['lightgrey'];

if (isset($_REQUEST['trackcol'])) $c_track=$col[$_REQUEST['trackcol']];
else $c_track=$col['blue'];

if (isset($_REQUEST['gridcol'])) $c_grid=$col[$_REQUEST['gridcol']];
else $c_grid=$col['verylightgrey'];

// transparent check 1.14.1
if ($transparent == 1)
   imagecolortransparent($im, $c_bg);

// fill background
imagefill($im,1,1,$c_bg);
	
function ierror($t)
{
global $im,$width,$height,$col;
ob_clean();
header("Content-type: image/png");
imagestring($im,2,($width/2)-20,$height/2,"ERROR: ".$t,$col['red']);
imagepng($im);
imagedestroy($im);
exit();
}

//ierror("Test");

$session=$NATS_Session->Check($NATS->DB);

if (!$session)
	{
	if ($NATS->Cfg->Get("site.graph.public",0)!=1)
		{
		ierror("Authorisation Failure");
		exit();
		}
	$key=$NATS->Cfg->Get("site.graph.key","");
	if (isset($_REQUEST['graphkey'])) $userkey=$_REQUEST['graphkey'];
	else $userkey="";
	
	if ( ($key!="") && ($key!=$userkey) )
		{
		ierror("Graph Key Failure");
		exit();
		}
	}


if (!isset($_REQUEST['nodeid'])) $nodeid="";
else $nodeid=$_REQUEST['nodeid'];

if (!isset($_REQUEST['testid'])) ierror("No test ID");

$day=date("d");
$month=date("m");
$year=date("Y");

if (isset($_REQUEST['startx'])) $startx=$_REQUEST['startx'];
else
	{ // 0:00 today    HMS mo da yr
	$startx=mktime(0,0,0,$month,$day,$year);
	//$startx=1203451396;
	}
	
if (isset($_REQUEST['finishx'])) $finishx=$_REQUEST['finishx'];
else $finishx=mktime(23,59,59,$month,$day,$year);

if ($startx<=0) $startx=time()+$startx;
if ($finishx<=0) $finishx=time()+$finishx;

//$finishx=1203454996;
$periodx=$finishx-$startx;
$startt=date("H:i:s d/m/y",$startx);
$finisht=date("H:i:s d/m/y",$finishx);


// titles and stuff
imagestring($im,2,2,2,$startt,$c_txt);
// -90 for size 1
imagestring($im,2,$width-108,2,$finisht,$c_txt);

if (isset($_REQUEST['title'])) $title=$_REQUEST['title'];
else $title=$nodeid;
$len=strlen($title)*4;
imagestring($im,4,($width/2)-$len,2,$title,$c_txt);

// offsets and lengths
$xoff=50+1;
$xlen=$width-$xoff-5;

$yoff=1;
$ylen=$height-$yoff-20;


// v-axes
imageline($im,50,ty(1),50,ty($height-20),$c_axes);
imageline($im,$width-5,ty(1),$width-5,ty($height-20),$c_axes);
// y-axes
imageline($im,50,ty(1),$width-5,ty(1),$c_axes);
imageline($im,$width-5,ty($height-20),50,ty($height-20),$c_axes);
//ierror("hello");


// range data

// Lowest

if (isset($_REQUEST['rangemin'])) $dlow=$_REQUEST['rangemin'];
else
	{
	$dlow=0;
	/*
	$q="SELECT testvalue FROM fnrecord WHERE testid=\"".ss($_REQUEST['testid'])."\"";
	$q.=" AND recordx>=".ss($startx)." AND recordx<=".ss($finishx)." ";
	$q.="ORDER BY testvalue ASC LIMIT 0,1"; // lowest
	$r=$NATS->DB->Query($q);
	//ierror($q);
	if (!$row=$NATS->DB->Fetch_Array($r)) ierror("No data for test");
	$lowest=$row['testvalue'];
	$dlow=$lowest;
	if ($dlow>0) $dlow=0;
	$NATS->DB->Free($r);
	*/
	}


// Highest

if (isset($_REQUEST['rangemax'])) $dhigh=$_REQUEST['rangemax'];
else
{
	$q="SELECT testvalue FROM fnrecord WHERE testid=\"".ss($_REQUEST['testid'])."\"";
	$q.=" AND recordx>=".ss($startx)." AND recordx<=".ss($finishx)." ";
	$q.= "ORDER BY testvalue DESC LIMIT 0,1"; //highest
	
	$r=$NATS->DB->Query($q);
	$row=$NATS->DB->Fetch_Array($r);
	$highest=$row['testvalue'];
	$dhigh=$highest;
	$NATS->DB->Free($r);
	if (isset($_REQUEST['rangemaxmin']))
		{
		if ($dhigh<$_REQUEST['rangemaxmin']) $dhigh=$_REQUEST['rangemaxmin'];
		}
}

$drange=$dhigh-$dlow;

// calculate scales
$xscale=$xlen/$periodx;
if ($drange>0) $yscale=$ylen/$drange;
else $yscale=1; // doesn't display but no change!

// Grid Lines

// Grid X - the X values

/* Oh if only I was actually any good at programming. There MUST be soooo many better ways to do these ranges
with all sorts of calculations to round to the nearest power of ten etc etc. Unfortunately I have the programming
skill of a walrus and typing code with tusks is difficult! */

/* TODO: Make this all like really elegant and stuff */

if ($draw_x_grid)
	{
	$xg_step=1;
	$xg_scale=1;
	if ($drange<0.001) $xg_scale=0.0001;
	else if ($drange<0.01) $xg_scale=0.001;
	else if ($drange<0.1) $xg_scale=0.01;
	else if ($drange<1) $xg_scale=0.1;
	else if ($drange<6) $xg_scale=1;
	else if ($drange<11) $xg_scale=2;
	else if ($drange<16) $xg_scale=4;
	else if ($drange<21) $xg_scale=5;
	else if ($drange<51) $xg_scale=10;
	else if ($drange<101) $xg_scale=20;
	else if ($drange<201) $xg_scale=50;
	else if ($drange<501) $xg_scale=100;
	else if ($drange<1001) $xg_scale=250;
	else if ($drange<10001) $xg_scale=1000;
	else if ($drange<100001) $xg_scale=10000;
	else if ($drange<1000001) $xg_scale=100000;
	else $draw_x_grid=false;
	
	if ($xg_scale<0) $xg_step=($xg_scale);
	}
	
if ($draw_x_grid)
	{
	//imagestring($im,1,2,ty(50),"Drawing X Grid ".$xg_scale,$c_txt);
	for ($a=$dlow; $a<=$dhigh; $a+=$xg_scale)
		{
		if (($a!=0)&&( ($a % $xg_scale) == 0))
			{
			//imagestring($im,1,2,ty(50+($a*5)),"Drawing X Grid ".$a,$c_txt);
			// draw a line
			imageline($im,posx($startx),ty(posy($a)),posx($finishx),ty(posy($a)),$c_grid);
//			imageline($im,$lastx,ty($lasty),$x,ty($y),$c_track);
			}
		}
	}

// Grid Y - the time values

if ($draw_y_grid)
	{
	$min=60;
	$hour=$min*60;
	$day=$hour*24;
	
	$syr=date("Y",$startx);
	$smo=date("m",$startx);
	$sda=date("d",$startx);
	$shr=date("H",$startx);
	// h m s mo da yr
	$lhr=mktime($shr,0,0,$smo,$sda,$syr);
	$lda=mktime(0,0,0,$smo,$sda,$syr);
	
	if ($periodx< (($hour*2)+1) )
		{
		$yg_scale=$min;
		$yg_start=$lhr;
		}
	else if ($periodx< (($day*2)+1) )
		{
		$yg_scale=$hour;
		$yg_start=$lhr;
		}
	else if ($periodx < ($day*32) )
		{
		$yg_scale=$day;
		$yg_start=$lda;
		}
	else $draw_y_grid=false;
	}
	
if ($draw_y_grid)
	{
	//imagestring($im,1,2,ty(50),"Drawing Y Grid ",$c_txt);
	for ($a=$yg_start; $a<$finishx; $a+=$yg_scale)
		{
		if ($a>$startx)
			{
			// draw line
			imageline($im,posx($a),ty(posy($dlow)),posx($a),ty(posy($dhigh)),$c_grid);
			}
		}
	}
function posx($time) // timex
{
global $xscale,$startx,$xoff;
$drawx=$xscale*($time-$startx);
$screenx=$drawx+$xoff;
$screenx=floor($screenx);
return $screenx;
}

function posy($value) 
{
global $yscale,$dlow,$yoff,$dhigh;
if ($value>$dhigh) $value=$dhigh;
$drawy=$yscale*($value-$dlow);
$screeny=$drawy+$yoff;
$screeny=floor($screeny);
return $screeny;
}



// show axes scales
imagestring($im,1,2,ty(10),$dlow,$c_txt);
imagestring($im,1,2,ty($height-18),$dhigh,$c_txt);


if (isset($_REQUEST['units'])) 
	{
	if (strpos($_REQUEST['units'],"/")===false)
		imagestring($im,1,2,ty($height-28),$_REQUEST['units'],$c_txt);
	

	else
		{
		$unit_array=explode("/",$_REQUEST['units']);
		$a=0;
		foreach($unit_array as $unit_string)
			{
			imagestring($im,1,2,ty($height-28-($a*8)),$unit_string,$c_txt);
			$a++;
			}
		}
	}	


// get data and draw
$q="SELECT testvalue,alertlevel,recordx FROM fnrecord WHERE testid=\"".ss($_REQUEST['testid'])."\" ";
$q.="AND recordx>=".ss($startx)." AND recordx<=".ss($finishx)." ORDER BY recordx ASC";
//$q.="LIMIT 0,100";
$r=$NATS->DB->Query($q);
$lastx=0;
$lasty=0;

$startval=0;
$finishval=0;

while ($row=mysqli_fetch_array($r))
	{
	$x=posx($row['recordx']);
	//$y=posy($row['testvalue']); 
	$val=$row['testvalue'];
	if ($val<0) $y=posy(0);
	else $y=posy($val);
	
	if ($row['alertlevel']==-1) $c=$col['grey'];
	else if ($row['alertlevel']==0) $c=$col['lightgreen'];
	else if ($row['alertlevel']==1) $c=$col['orange'];
	else if ($row['alertlevel']==2) $c=$col['red'];
	else $c=$col['black'];
	
	// up lines
	if ($draw_spike==1) imageline($im,$x,ty(0),$x,ty($y),$c);

	
		
	if ($lastx!=0)
		{
			
		// join-the-dots
		if ($draw_track==1) imageline($im,$lastx,ty($lasty),$x,ty($y),$c_track);
		
		// fill -- DOES NOT WORK
		//if (($draw_fill==1)&&($draw_spike==1)&&($draw_track==1))
			//imagefill($im,$x-1,ty(1),$c);
		
		// bottom line
		if ($draw_under==1)
			{
			if (($row['alertlevel']>0)||
				 (($row['alertlevel']==0)&&($draw_under_pass==1)) )
				{
				imageline($im,$lastx,ty(1),$x,ty(1),$c);
				imageline($im,$lastx,ty(2),$x,ty(2),$c);
				}
			}
		}	
	
	//imageellipse($im,$x,ty($y),1,1,$c_red);
	
	$lastx=$x;
	$lasty=$y;
	
	}
mysqli_free_result($r);

	

// output image
ob_clean();
header("Content-type: image/png");
imagepng($im);
imagedestroy($im);
exit(); // just in case

?>
