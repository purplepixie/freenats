<?php
/* -------------------------------------------------------------
This file is part of FreeNATS

FreeNATS is (C) Copyright 2008-2010 PurplePixie Systems

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

class TNATS_Lang
{
var $items;
var $languages;
var $language;
var $baselanguage="en";

function GetLanguages()
{
global $BaseDir;
if ( !is_array($this->languages) )
	{
	$pathlen=strlen($BaseDir."lang/");
	$this->languages=array();
	foreach(glob($BaseDir."lang/*.lang.php") as $langfile)
		{
		$langfile=substr($langfile,$pathlen);
		$parts=explode(".",$langfile);
		if (count($parts)==4) // valid language format Language.code.lang.php
			{
			$this->languages[$parts[1]]=$parts[0];
			}
		}
	}
return $this->languages;
}

function Load($language="")
{
global $BaseDir,$NATS;
$this->GetLanguages();
if ($language == "") $language=$this->baselanguage;
if ($language != $this->baselanguage) $this->Load($this->baselanguage);
if (!is_array($this->items)) $this->items=array();

if ( isset($this->languages[$language]) )
	{
	$file=$BaseDir."lang/".$this->languages[$language].".".$language.".lang.php";
	if (file_exists($file))
		{
		$lang=array();
		include_once($file);
		$this->items = array_merge($this->items, $lang);
		}
	else $NATS->Event("Language file ".$file." not found",5,"Language","Load");
	}
else $NATS->Event("Illegal Language ".$language,5,"Language","Load");
}

function Item($item)
{
global $NATS;
//return "{".$item."}";
if (isset($this->items[$item])) return $this->items[$item];
else
	{
	$NATS->PageError("missing lang.element {ULE}",$item);
	return "ULE{".$item."}";
	}
}

}

?>